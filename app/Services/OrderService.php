<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\AbandonedCart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(protected CouponService $couponService, protected InventoryService $inventory) {}

    /**
     * @throws InsufficientStockException
     */
    public function createOrder(Collection $cartItems, array $addressData, string $paymentMethod, ?Coupon $coupon = null): Order
    {
        $order = DB::transaction(function () use ($cartItems, $addressData, $paymentMethod, $coupon) {
            $this->lockAndVerifyStock($cartItems);

            $subtotal = $cartItems->sum(function ($item) {
                $price = $item->variant?->sale_price ?? $item->variant?->price
                    ?? $item->product->sale_price ?? $item->product->price;

                return (float) $price * $item->quantity;
            });

            $discount = $coupon ? $this->couponService->calculateDiscount($coupon, $subtotal) : 0;
            $shipping = $this->calculateShipping($subtotal, $discount);
            $tax = $this->calculateTax($cartItems, $subtotal, $discount);
            $total = $subtotal - $discount + $shipping + $tax;

            $order = Order::create(array_merge($addressData, [
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_id' => $coupon?->id,
                'shipping_amount' => $shipping,
                'tax_amount' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]));

            foreach ($cartItems as $item) {
                $price = $item->variant?->sale_price ?? $item->variant?->price
                    ?? $item->product->sale_price ?? $item->product->price;

                $order->items()->create([
                    'product_id' => $item->product->id,
                    'variant_id' => $item->variant?->id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'unit_price' => $price,
                    'quantity' => $item->quantity,
                    'line_total' => $price * $item->quantity,
                ]);

                $this->inventory->recordMovement(
                    $item->product,
                    'sale',
                    -$item->quantity,
                    null,
                    Auth::id(),
                    $order,
                    $item->variant,
                );

                $item->product->increment('total_sold', $item->quantity);
            }

            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => 'pending',
                'comment' => 'Order placed.',
            ]);

            if ($coupon) {
                $this->couponService->incrementUsage($coupon->id);
            }

            return $order;
        });

        if ($order->user_id) {
            AbandonedCart::where('user_id', $order->user_id)
                ->where('status', 'active')
                ->update(['status' => 'recovered', 'recovered_order_id' => $order->id]);
        }

        if (settings('send_order_emails', true)) {
            SendOrderConfirmationEmail::dispatch($order);
        }

        return $order;
    }

    /**
     * Locks each cart item's product/variant row for the rest of the
     * enclosing transaction and re-checks stock against the live, locked
     * value — not the possibly-stale amount loaded onto the cart item.
     * Locking here (before any stock is decremented) is what stops two
     * concurrent checkouts from both succeeding on the last unit: the
     * second transaction blocks on the row lock until the first commits
     * its decrement, then sees the now-insufficient stock and fails.
     *
     * @throws InsufficientStockException
     */
    protected function lockAndVerifyStock(Collection $cartItems): void
    {
        foreach ($cartItems as $item) {
            if ($item->variant) {
                $variant = ProductVariant::whereKey($item->variant->id)->lockForUpdate()->first();

                if (! $variant || $variant->stock < $item->quantity) {
                    throw new InsufficientStockException("Not enough stock for {$item->product->name} ({$item->variant->name}).");
                }
            } else {
                $product = Product::whereKey($item->product->id)->lockForUpdate()->first();

                if (! $product || $product->stock < $item->quantity) {
                    throw new InsufficientStockException("Not enough stock for {$item->product->name}.");
                }
            }
        }
    }

    /**
     * Flat-rate shipping with a free-shipping threshold, both configurable
     * via site settings. Shared by cart/checkout previews and order
     * creation so the quoted shipping cost always matches what gets charged.
     */
    public function calculateShipping(float $subtotal, float $discount): float
    {
        $threshold = (float) settings('free_shipping_threshold', 999);
        $flatRate = (float) settings('flat_shipping_rate', 49);

        return ($subtotal - $discount) >= $threshold ? 0 : $flatRate;
    }

    /**
     * GST is calculated per line item against its own product's tax_rate,
     * applied to that item's proportional share of the post-discount
     * subtotal — this keeps mixed tax rates correct instead of applying a
     * single flat rate to the whole order.
     */
    public function calculateTax(Collection $cartItems, float $subtotal, float $discount): float
    {
        if ($subtotal <= 0) {
            return 0;
        }

        $tax = $cartItems->sum(function ($item) use ($subtotal, $discount) {
            $price = $item->variant?->sale_price ?? $item->variant?->price
                ?? $item->product->sale_price ?? $item->product->price;

            $itemSubtotal = (float) $price * $item->quantity;
            $itemDiscountShare = ($itemSubtotal / $subtotal) * $discount;
            $taxableAmount = $itemSubtotal - $itemDiscountShare;

            return $taxableAmount * ((float) $item->product->tax_rate / 100);
        });

        return round($tax, 2);
    }

    public function updateStatus(Order $order, string $newStatus, ?string $comment = null, ?int $adminId = null): void
    {
        $from = $order->order_status;

        $order->update(['order_status' => $newStatus]);

        $order->statusHistory()->create([
            'from_status' => $from,
            'to_status' => $newStatus,
            'comment' => $comment,
            'changed_by' => $adminId,
        ]);
    }

    /**
     * Restores stock for every item on a cancelled order. Called once per
     * cancellation — callers are responsible for guarding against calling
     * this more than once for the same order.
     */
    public function restockCancelledOrder(Order $order, ?int $actorId = null): void
    {
        foreach ($order->items()->with(['product', 'variant'])->get() as $item) {
            if (! $item->product) {
                continue;
            }

            $this->inventory->recordMovement(
                $item->product,
                'return',
                $item->quantity,
                'Order cancelled',
                $actorId,
                $order,
                $item->variant,
            );
        }
    }
}
