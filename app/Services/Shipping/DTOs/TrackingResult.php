<?php

namespace App\Services\Shipping\DTOs;

readonly class TrackingResult
{
    public function __construct(
        public ?string $status,
        public array $events = [],
        public bool $delivered = false,
    ) {}
}
