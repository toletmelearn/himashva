<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShiprocketService implements ShippingServiceInterface
{
    protected function token(): ?string
    {
        $email = settings('shiprocket_email');
        $password = settings('shiprocket_password');

        if (! $email || ! $password) {
            return null;
        }

        $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->successful() ? $response->json('token') : null;
    }

    public function calculateShipping(string $pincode, int $weightGrams, bool $cod = false): float
    {
        $subtotalThreshold = (float) settings('free_shipping_threshold', 999);
        $flatRate = (float) settings('flat_shipping_rate', 49);

        $token = $this->token();

        if (! $token) {
            return $flatRate;
        }

        $response = Http::withToken($token)->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability', [
            'pickup_postcode' => '110001',
            'delivery_postcode' => $pincode,
            'weight' => max($weightGrams, 100) / 1000,
            'cod' => $cod ? 1 : 0,
        ]);

        if ($response->successful()) {
            $rate = $response->json('data.available_courier_companies.0.rate');

            if ($rate) {
                return (float) $rate;
            }
        }

        return $flatRate;
    }

    public function createShipment(Order $order): ?string
    {
        $token = $this->token();

        if (! $token) {
            return 'HMV'.strtoupper(Str::random(8));
        }

        $response = Http::withToken($token)->post('https://apiv2.shiprocket.in/v1/external/orders/create/adhoc', [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'billing_customer_name' => $order->name,
            'billing_address' => $order->address_line_1,
            'billing_city' => $order->city,
            'billing_pincode' => $order->postal_code,
            'billing_state' => $order->state,
            'billing_country' => $order->country,
            'billing_email' => $order->email,
            'billing_phone' => $order->phone,
            'shipping_is_billing' => true,
            'order_items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'sku' => $item->sku,
                'units' => $item->quantity,
                'selling_price' => $item->unit_price,
            ])->toArray(),
            'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'sub_total' => $order->total,
        ]);

        return $response->successful() ? $response->json('shipment_id') : null;
    }

    public function trackShipment(string $trackingNumber): array
    {
        $token = $this->token();

        if (! $token) {
            return ['status' => 'unknown'];
        }

        $response = Http::withToken($token)->get("https://apiv2.shiprocket.in/v1/external/courier/track/shipment/{$trackingNumber}");

        return $response->successful() ? $response->json() : ['status' => 'unknown'];
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $token = $this->token();

        if (! $token) {
            return false;
        }

        $response = Http::withToken($token)->post('https://apiv2.shiprocket.in/v1/external/orders/cancel', [
            'ids' => [$trackingNumber],
        ]);

        return $response->successful();
    }
}
