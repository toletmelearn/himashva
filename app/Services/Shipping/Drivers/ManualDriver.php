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
use Illuminate\Support\Str;

class ManualDriver implements ShippingDriverInterface
{
    public function authenticate(ShippingProvider $config): bool
    {
        return true;
    }

    public function checkPincode(string $pincode, ShippingProvider $config): PincodeResult
    {
        return new PincodeResult(available: true, estimatedDays: 5, codAvailable: true);
    }

    public function calculateRate(ShippingRateRequest $request, ShippingProvider $config): ShippingRateResult
    {
        $flatRate = (float) $config->getSetting('flat_rate', 49);
        $freeAbove = $config->getSetting('free_above');

        $rate = ($freeAbove !== null && $request->orderValue >= (float) $freeAbove) ? 0.0 : $flatRate;

        return new ShippingRateResult(rate: $rate, estimatedDays: 5, courierName: 'Manual Delivery');
    }

    public function createShipment(Order $order, ShippingProvider $config): ShipmentResult
    {
        $awb = 'MANUAL-'.strtoupper(Str::random(10));

        return new ShipmentResult(
            success: true,
            shipmentId: $awb,
            awbNumber: $awb,
            labelUrl: null,
            trackingUrl: null,
        );
    }

    public function getTracking(string $awbNumber, ShippingProvider $config): TrackingResult
    {
        return new TrackingResult(status: null, events: [], delivered: false);
    }

    public function cancelShipment(string $shipmentId, ShippingProvider $config): bool
    {
        return true;
    }

    public function getLabel(string $shipmentId, ShippingProvider $config): ?string
    {
        return null;
    }
}
