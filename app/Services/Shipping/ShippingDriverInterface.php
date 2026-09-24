<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\ShippingProvider;
use App\Services\Shipping\DTOs\PincodeResult;
use App\Services\Shipping\DTOs\ShipmentResult;
use App\Services\Shipping\DTOs\ShippingRateRequest;
use App\Services\Shipping\DTOs\ShippingRateResult;
use App\Services\Shipping\DTOs\TrackingResult;

interface ShippingDriverInterface
{
    public function authenticate(ShippingProvider $config): bool;

    public function checkPincode(string $pincode, ShippingProvider $config): PincodeResult;

    public function calculateRate(ShippingRateRequest $request, ShippingProvider $config): ShippingRateResult;

    public function createShipment(Order $order, ShippingProvider $config): ShipmentResult;

    public function getTracking(string $awbNumber, ShippingProvider $config): TrackingResult;

    public function cancelShipment(string $shipmentId, ShippingProvider $config): bool;

    public function getLabel(string $shipmentId, ShippingProvider $config): ?string;
}
