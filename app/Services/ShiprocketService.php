<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        try {
            $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);
        } catch (\Throwable $e) {
            Log::error('Shiprocket authentication failed', ['message' => $e->getMessage()]);

            return null;
        }

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

        try {
            $response = Http::withToken($token)->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability', [
                'pickup_postcode' => '110001',
                'delivery_postcode' => $pincode,
                'weight' => max($weightGrams, 100) / 1000,
                'cod' => $cod ? 1 : 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Shiprocket serviceability check failed', ['pincode' => $pincode, 'message' => $e->getMessage()]);

            return $flatRate;
        }

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

        try {
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
        } catch (\Throwable $e) {
            Log::error('Shiprocket shipment creation failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? $response->json('shipment_id') : null;
    }

    public function trackShipment(string $trackingNumber): array
    {
        $token = $this->token();

        if (! $token) {
            return ['status' => 'unknown'];
        }

        try {
            $response = Http::withToken($token)->get("https://apiv2.shiprocket.in/v1/external/courier/track/shipment/{$trackingNumber}");
        } catch (\Throwable $e) {
            Log::error('Shiprocket shipment tracking failed', ['tracking_number' => $trackingNumber, 'message' => $e->getMessage()]);

            return ['status' => 'unknown'];
        }

        return $response->successful() ? $response->json() : ['status' => 'unknown'];
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $token = $this->token();

        if (! $token) {
            return false;
        }

        try {
            $response = Http::withToken($token)->post('https://apiv2.shiprocket.in/v1/external/orders/cancel', [
                'ids' => [$trackingNumber],
            ]);
        } catch (\Throwable $e) {
            Log::error('Shiprocket shipment cancellation failed', ['tracking_number' => $trackingNumber, 'message' => $e->getMessage()]);

            return false;
        }

        return $response->successful();
    }
}
