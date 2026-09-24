<?php

namespace App\Services\Payment\DTOs;

readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $transactionId,
        public float $amount,
        public ?string $method = null,
        public array $rawResponse = [],
        public ?string $message = null,
    ) {}
}
