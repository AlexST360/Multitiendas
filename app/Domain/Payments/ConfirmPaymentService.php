<?php

namespace App\Domain\Payments;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\DTO\ConfirmedPayment;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConfirmPaymentService
{
    public function confirmDto(ConfirmedPayment $dto): Payment
    {
        return $this->confirm([
            'store_id' => $dto->storeId,
            'order_id' => $dto->orderId,
            'gateway' => $dto->gateway,
            'gateway_reference' => $dto->gatewayReference,
            'amount' => $dto->amount,
            'currency' => $dto->currency,
            'idempotency_key' => $dto->idempotencyKey,
            'raw_payload' => $dto->rawPayload,
        ]);
    }

    public function confirm(array $data): Payment
    {
        /*
        |--------------------------------------------------------------------------
        | 0. Idempotencia FIRST (antes de validar estado)
        |--------------------------------------------------------------------------
        | Si el gateway reintenta el webhook, y nosotros ya procesamos este intento,
        | devolvemos el mismo Payment aunque la orden ya esté en "paid".
        */
        $existing = Payment::query()
            ->where('store_id', $data['store_id'])
            ->where('idempotency_key', $data['idempotency_key'])
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($data) {

            $order = Order::query()
                ->where('store_id', $data['store_id'])
                ->where('id', $data['order_id'])
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 1. Si ya está pagada, no aceptamos otra idempotency_key
            |--------------------------------------------------------------------------
            */
            if ($order->status === OrderStatus::Paid) {
                throw new \DomainException('La orden ya está pagada.');
            }

            if ($order->status !== OrderStatus::PendingPayment) {
                throw new \DomainException('La orden no está en estado válido para confirmar pago.');
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Validar moneda (normalizada)
            |--------------------------------------------------------------------------
            */
            $currency = strtoupper($data['currency'] ?? ($order->currency ?? 'CLP'));

            if (strtoupper($order->currency ?? 'CLP') !== $currency) {
                throw new \InvalidArgumentException('Moneda inválida para esta orden.');
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Validar monto (histórico sagrado)
            |--------------------------------------------------------------------------
            */
            if ((int) $data['amount'] !== (int) $order->total) {
                throw new \InvalidArgumentException('Monto inválido para esta orden.');
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Re-chequeo idempotencia dentro de la transacción (concurrencia)
            |--------------------------------------------------------------------------
            */
            $existing = Payment::query()
                ->where('store_id', $data['store_id'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing) {
                return $existing;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Crear Payment aprobado
            |--------------------------------------------------------------------------
            */
            try {
                $payment = Payment::create([
                    'store_id' => $data['store_id'],
                    'order_id' => $order->id,
                    'gateway' => $data['gateway'],
                    'gateway_reference' => $data['gateway_reference'] ?? null,
                    'status' => PaymentStatus::Approved,
                    'currency' => $currency,
                    'amount' => (int) $data['amount'],
                    'idempotency_key' => $data['idempotency_key'],
                    'raw_response' => $data['raw_payload'] ?? null,
                ]);
            } catch (QueryException $e) {
                // Carrera por unique constraint (store_id, idempotency_key)
                $payment = Payment::query()
                    ->where('store_id', $data['store_id'])
                    ->where('idempotency_key', $data['idempotency_key'])
                    ->first();

                if ($payment) {
                    return $payment;
                }

                throw $e;
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Marcar orden pagada + paid_at (si existe)
            |--------------------------------------------------------------------------
            */
            $update = [
                'status' => OrderStatus::Paid,
            ];

            if (Schema::hasColumn('orders', 'paid_at')) {
                $update['paid_at'] = now();
            }

            $order->update($update);

            return $payment;
        });
    }
}