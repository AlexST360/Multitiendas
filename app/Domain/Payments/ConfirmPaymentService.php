<?php

namespace App\Domain\Payments;

use App\Domain\Payments\Exceptions\PaymentAmountMismatchException;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ConfirmPaymentService
{
    /**
     * Confirma un pago aprobado por un gateway.
     *
     * - Aplica idempotencia
     * - Valida monto y moneda
     * - Crea registro Payment
     * - Cambia estado de Order a Paid
     */
    public function handle(
        Order $order,
        string $gateway,
        string $gatewayReference,
        int $amount,
        string $currency,
        string $idempotencyKey,
        array $rawResponse = []
    ): Payment {
        return DB::transaction(function () use (
            $order,
            $gateway,
            $gatewayReference,
            $amount,
            $currency,
            $idempotencyKey,
            $rawResponse
        ) {

            //  Idempotencia fuerte por store + idempotency_key
            $existing = Payment::where('store_id', $order->store_id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            //  Validación de monto
            if ($order->total !== $amount || $order->currency !== $currency) {
                throw new PaymentAmountMismatchException(
                    orderId: $order->id,
                    expectedAmount: $order->total,
                    receivedAmount: $amount,
                    currency: $currency,
                    gatewayReference: $gatewayReference
                );
            }

            //  Crear pago
            $payment = Payment::create([
                'store_id' => $order->store_id,
                'order_id' => $order->id,
                'gateway' => $gateway,
                'gateway_reference' => $gatewayReference,
                'status' => PaymentStatus::Approved,
                'currency' => $currency,
                'amount' => $amount,
                'idempotency_key' => $idempotencyKey,
                'raw_response' => $rawResponse,
            ]);

            //  Marcar orden como pagada (si no lo está)
            if ($order->status !== OrderStatus::Paid) {
                $order->update([
                    'status' => OrderStatus::Paid,
                ]);
            }

            return $payment;
        });
    }
}