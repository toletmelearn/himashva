<?php

namespace App\Services\Payment\DTOs;

readonly class RefundResult
{
    public function __construct(
        public bool $success,
        public ?string $refundId,
        public float $amount,
        public array $rawResponse = [],
    ) {}
}
