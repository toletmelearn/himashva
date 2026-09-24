<?php

namespace App\Services\Shipping\DTOs;

readonly class ShippingRateResult
{
    public function __construct(
        public float $rate,
        public ?int $estimatedDays = null,
        public ?string $courierName = null,
    ) {}
}
