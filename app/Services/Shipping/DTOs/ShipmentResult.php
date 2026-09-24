<?php

namespace App\Services\Shipping\DTOs;

readonly class ShipmentResult
{
    public function __construct(
        public bool $success,
        public ?string $shipmentId = null,
        public ?string $awbNumber = null,
        public ?string $labelUrl = null,
        public ?string $trackingUrl = null,
    ) {}
}
