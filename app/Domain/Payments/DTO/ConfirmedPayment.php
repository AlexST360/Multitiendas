<?php

namespace App\Domain\Payments\DTO;

class ConfirmedPayment
{
    public function __construct(
        public int $storeId,
        public int $orderId,
        public string $gateway,
        public ?string $gatewayReference,
        public int $amount,
        public string $currency,
        public string $idempotencyKey,
        public array $rawPayload = []
    ) {}
}