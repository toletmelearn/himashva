<?php

namespace App\Services\Shipping\DTOs;

readonly class PincodeResult
{
    public function __construct(
        public bool $available,
        public ?int $estimatedDays = null,
        public bool $codAvailable = false,
    ) {}
}
