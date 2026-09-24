<?php

namespace App\Services\Shipping\Drivers;

use App\Models\Order;
use App\Models\ShippingProvider;
use App\Services\Shipping\DTOs\PincodeResult;
use App\Services\Shipping\DTOs\ShipmentResult;
use App\Services\Shipping\DTOs\ShippingRateRequest;
use App\Services\Shipping\DTOs\ShippingRateResult;
use App\Services\Shipping\DTOs\TrackingResult;
use App\Services\Shipping\ShippingDriverInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ShiprocketDriver implements ShippingDriverInterface
{
    protected function token(ShippingProvider $config): ?string
    {
        $email = $config->getCredential('email');
        $password = $config->getCredential('password');

        if (! $email || ! $password) {
            return null;
        }

        return Cache::remember("shiprocket_token_{$config->id}", now()->addHours(23), function () use ($email, $password) {
            $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);

            return $response->successful() ? $response->json('token') : null;
        });
    }

    public function authenticate(ShippingProvider $config): bool
    {
        return filled($this->token($config));
    }

    public function checkPincode(string $pincode, ShippingProvider $config): PincodeResult
    {
        $token = $this->token($config);

        if (! $token) {
            return new PincodeResult(available: false);
        }

        $response = Http::withToken($token)->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability', [
            'pickup_postcode' => $config->getSetting('pickup_pincode', '110001'),
            'delivery_postcode' => $pincode,
            'weight' => 0.5,
            'cod' => 0,
        ]);

        if (! $response->successful()) {
            return new PincodeResult(available: false);
        }

        $available = (bool) $response->json('data.available_courier_companies.0');

        return new PincodeResult(
            available: $available,
            estimatedDays: $response->json('data.available_courier_companies.0.etd') ? (int) $response->json('data.available_courier_companies.0.estimated_delivery_days') : null,
            codAvailable: (bool) $response->json('data.available_courier_companies.0.cod', false),
        );
    }

    public function calculateRate(ShippingRateRequest $request, ShippingProvider $config): ShippingRateResult
    {
        $token = $this->token($config);

        if (! $token) {
            return new ShippingRateResult(rate: (float) $config->getSetting('flat_rate', 49));
        }

        $response = Http::withToken($token)->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability', [
            'pickup_postcode' => $config->getSetting('pickup_pincode', '110001'),
            'delivery_postcode' => $request->pincode,
            'weight' => max($request->weightGrams, 100) / 1000,
            'cod' => $request->cod ? 1 : 0,
        ]);

        if ($response->successful() && $response->json('data.available_courier_companies.0.rate')) {
            return new ShippingRateResult(
                rate: (float) $response->json('data.available_courier_companies.0.rate'),
                estimatedDays: (int) $response->json('data.available_courier_companies.0.estimated_delivery_days', 0) ?: null,
                courierName: $response->json('data.available_courier_companies.0.courier_name'),
            );
        }

        return new ShippingRateResult(rate: (float) $config->getSetting('flat_rate', 49));
    }

    public function createShipment(Order $order, ShippingProvider $config): ShipmentResult
    {
        $token = $this->token($config);

        if (! $token) {
            return new ShipmentResult(success: false);
        }

        $response = Http::withToken($token)->post('https://apiv2.shiprocket.in/v1/external/orders/create/adhoc', [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => $config->getSetting('pickup_address', 'Primary'),
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
            'length' => $config->getSetting('default_length_cm', 10),
            'breadth' => $config->getSetting('default_breadth_cm', 10),
            'height' => $config->getSetting('default_height_cm', 10),
            'weight' => ($config->getSetting('default_weight_grams', 500)) / 1000,
        ]);

        if (! $response->successful()) {
            return new ShipmentResult(success: false);
        }

        return new ShipmentResult(
            success: true,
            shipmentId: (string) $response->json('shipment_id'),
            awbNumber: $response->json('awb_code'),
            trackingUrl: $response->json('awb_code') ? "https://shiprocket.co/tracking/{$response->json('awb_code')}" : null,
        );
    }

    public function getTracking(string $awbNumber, ShippingProvider $config): TrackingResult
    {
        $token = $this->token($config);

        if (! $token) {
            return new TrackingResult(status: null);
        }

        $response = Http::withToken($token)->get("https://apiv2.shiprocket.in/v1/external/courier/track/awb/{$awbNumber}");

        if (! $response->successful()) {
            return new TrackingResult(status: null);
        }

        $data = $response->json();
        $status = $data['tracking_data']['track_status'] ?? null;
        $events = $data['tracking_data']['shipment_track'] ?? [];

        return new TrackingResult(
            status: $status,
            events: is_array($events) ? $events : [],
            delivered: ($data['tracking_data']['delivered_date'] ?? null) !== null,
        );
    }

    public function cancelShipment(string $shipmentId, ShippingProvider $config): bool
    {
        $token = $this->token($config);

        if (! $token) {
            return false;
        }

        $response = Http::withToken($token)->post('https://apiv2.shiprocket.in/v1/external/orders/cancel', [
            'ids' => [$shipmentId],
        ]);

        return $response->successful();
    }

    public function getLabel(string $shipmentId, ShippingProvider $config): ?string
    {
        $token = $this->token($config);

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)->post('https://apiv2.shiprocket.in/v1/external/courier/generate/label', [
            'shipment_id' => [$shipmentId],
        ]);

        return $response->successful() ? $response->json('label_url') : null;
    }
}
