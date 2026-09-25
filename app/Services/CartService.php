<?php

namespace App\Services;

use App\Models\AbandonedCart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_KEY = 'guest_cart';

    public function getItems(): Collection
    {
        if (Auth::check()) {
            return CartItem::with(['product.images', 'variant'])
                ->where('user_id', Auth::id())
                ->get();
        }

        return collect(Session::get(self::SESSION_KEY, []))
            ->map(function (array $row) {
                $product = Product::with('images')->find($row['product_id']);
                $variant = $row['variant_id'] ? ProductVariant::find($row['variant_id']) : null;

                if (! $product) {
                    return null;
                }

                return (object) [
                    'id' => $row['product_id'].'-'.($row['variant_id'] ?? '0'),
                    'product_id' => $product->id,
                    'variant_id' => $row['variant_id'],
                    'quantity' => $row['quantity'],
                    'product' => $product,
                    'variant' => $variant,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return array{0: bool, 1: ?string} [success, error message]
     */
    public function add(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        $product = Product::find($productId);

        if (! $product || $product->status !== 'active') {
            return [false, 'Product is out of stock.'];
        }

        if ($variantId) {
            $variant = ProductVariant::find($variantId);

            if (! $variant || $variant->stock < $quantity) {
                return [false, 'Product is out of stock.'];
            }
        } elseif ($product->stock < $quantity) {
            return [false, 'Product is out of stock.'];
        }

        if (Auth::check()) {
            $item = CartItem::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->first();

            if ($item) {
                $item->increment('quantity', $quantity);
            } else {
                CartItem::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                ]);
            }

            $this->snapshotCart();

            return [true, null];
        }

        $cart = Session::get(self::SESSION_KEY, []);
        $key = $productId.'-'.($variantId ?? '0');

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = ['product_id' => $productId, 'variant_id' => $variantId, 'quantity' => $quantity];
        }

        Session::put(self::SESSION_KEY, $cart);

        $this->snapshotCart();

        return [true, null];
    }

    /**
     * @return array{0: bool, 1: ?string} [success, error message]
     */
    public function update(string $itemId, int $quantity): array
    {
        $quantity = max(1, $quantity);

        if (Auth::check()) {
            $item = CartItem::where('id', $itemId)->where('user_id', Auth::id())->first();

            if (! $item) {
                return [false, 'Item not found.'];
            }

            [$available, $error] = $this->availableStock($item->product_id, $item->variant_id);

            if ($available !== null && $quantity > $available) {
                return [false, $error];
            }

            $item->update(['quantity' => $quantity]);
            $this->snapshotCart();

            return [true, null];
        }

        $cart = Session::get(self::SESSION_KEY, []);

        if (! isset($cart[$itemId])) {
            return [false, 'Item not found.'];
        }

        [$available, $error] = $this->availableStock($cart[$itemId]['product_id'], $cart[$itemId]['variant_id']);

        if ($available !== null && $quantity > $available) {
            return [false, $error];
        }

        $cart[$itemId]['quantity'] = $quantity;
        Session::put(self::SESSION_KEY, $cart);
        $this->snapshotCart();

        return [true, null];
    }

    /**
     * @return array{0: ?int, 1: ?string} [available stock, error message if product/variant is gone]
     */
    protected function availableStock(int $productId, ?int $variantId): array
    {
        if ($variantId) {
            $variant = ProductVariant::find($variantId);

            return $variant ? [$variant->stock, 'Only '.$variant->stock.' left in stock.'] : [0, 'This item is no longer available.'];
        }

        $product = Product::find($productId);

        return $product ? [$product->stock, 'Only '.$product->stock.' left in stock.'] : [0, 'This item is no longer available.'];
    }

    public function remove(string $itemId): void
    {
        if (Auth::check()) {
            CartItem::where('id', $itemId)->where('user_id', Auth::id())->delete();

            return;
        }

        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$itemId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function getSubtotal(): float
    {
        return $this->getItems()->sum(function ($item) {
            $price = $item->variant?->sale_price ?? $item->variant?->price
                ?? $item->product->sale_price ?? $item->product->price;

            return (float) $price * $item->quantity;
        });
    }

    public function getCount(): int
    {
        return (int) $this->getItems()->sum('quantity');
    }

    public function clear(): void
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->delete();
        }

        Session::forget(self::SESSION_KEY);
    }

    public function mergeCarts(int $userId): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        foreach ($cart as $row) {
            $item = CartItem::where('user_id', $userId)
                ->where('product_id', $row['product_id'])
                ->where('variant_id', $row['variant_id'])
                ->first();

            if ($item) {
                $item->increment('quantity', $row['quantity']);
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'quantity' => $row['quantity'],
                ]);
            }
        }

        Session::forget(self::SESSION_KEY);
    }

    /**
     * Snapshots the current cart into an AbandonedCart record for the
     * logged-in user, so a recovery reminder can be sent if they leave
     * without checking out. Guests are not tracked here — their cart
     * only gets an AbandonedCart row once they provide an email at
     * checkout (see CheckoutController::saveEmail).
     */
    public function snapshotCart(): void
    {
        if (! Auth::check()) {
            return;
        }

        $items = $this->getItems();

        if ($items->isEmpty()) {
            AbandonedCart::where('user_id', Auth::id())->where('status', 'active')->delete();

            return;
        }

        [$cartData, $total] = $this->buildCartSnapshotData($items);

        AbandonedCart::updateOrCreate(
            ['user_id' => Auth::id(), 'status' => 'active'],
            [
                'session_id' => session()->getId(),
                'email' => Auth::user()->email,
                'cart_data' => $cartData,
                'total' => $total,
                'expires_at' => now()->addDays(30),
            ]
        );
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: float}
     */
    public function buildCartSnapshotData(?Collection $items = null): array
    {
        $items ??= $this->getItems();

        $cartData = $items->map(function ($item) {
            $price = $item->variant?->sale_price ?? $item->variant?->price
                ?? $item->product->sale_price ?? $item->product->price;

            $image = $item->product->images->first();
            $imageUrl = ($image && $image->image_path !== 'placeholder.jpg')
                ? asset('storage/'.$image->image_path)
                : null;

            return [
                'product_id' => $item->product->id,
                'variant_id' => $item->variant?->id,
                'product_name' => $item->product->name,
                'variant_name' => $item->variant?->name,
                'quantity' => $item->quantity,
                'price' => (float) $price,
                'image_url' => $imageUrl,
            ];
        })->values()->all();

        $total = $items->sum(function ($item) {
            $price = $item->variant?->sale_price ?? $item->variant?->price
                ?? $item->product->sale_price ?? $item->product->price;

            return (float) $price * $item->quantity;
        });

        return [$cartData, $total];
    }
}
