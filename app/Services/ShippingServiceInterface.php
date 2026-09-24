<?php

namespace App\Services;

use App\Models\Order;

interface ShippingServiceInterface
{
    public function calculateShipping(string $pincode, int $weightGrams, bool $cod = false): float;

    public function createShipment(Order $order): ?string;

    public function trackShipment(string $trackingNumber): array;

    public function cancelShipment(string $trackingNumber): bool;
}
