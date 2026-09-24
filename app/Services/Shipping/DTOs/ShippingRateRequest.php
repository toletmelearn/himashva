<?php

namespace App\Services\Shipping\DTOs;

readonly class ShippingRateRequest
{
    public function __construct(
        public string $pincode,
        public int $weightGrams,
        public float $orderValue = 0,
        public bool $cod = false,
    ) {}
}
