<?php

namespace App\Domain\Payments\Exceptions;

use RuntimeException;

class PaymentAmountMismatchException extends RuntimeException
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $expectedAmount,
        public readonly int $receivedAmount,
        public readonly string $currency,
        public readonly string $gatewayReference
    ) {
        parent::__construct($this->buildMessage());
    }

    protected function buildMessage(): string
    {
        return sprintf(
            'Payment amount mismatch for order #%d. Expected: %d %s, Received: %d %s. Gateway reference: %s',
            $this->orderId,
            $this->expectedAmount,
            $this->currency,
            $this->receivedAmount,
            $this->currency,
            $this->gatewayReference
        );
    }
}