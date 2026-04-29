<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\DTO\ConfirmedPayment;
use App\Domain\Payments\Gateways\Contracts\GatewayInterface;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FakeGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'fake';
    }

    /**
     * Parsea el payload del webhook fake.
     *
     * El FakeGateway no hace llamadas externas: confía en los datos
     * del request directamente (solo disponible en local/staging).
     *
     * Campos esperados en el request:
     *   - idempotency_key  string requerido
     *   - gateway_reference string|null opcional
     */
    public function parseWebhook(Request $request, Store $store, Order $order): ConfirmedPayment
    {
        $idempotencyKey = trim((string) ($request->input('idempotency_key') ?? ''));

        if ($idempotencyKey === '') {
            throw new \InvalidArgumentException('El campo idempotency_key es obligatorio para el gateway fake.');
        }

        return new ConfirmedPayment(
            storeId: (int) $store->id,
            orderId: (int) $order->id,
            gateway: $this->name(),
            gatewayReference: $request->input('gateway_reference') ?? ('fake_ref_' . Str::uuid()),
            amount: (int) $order->total,
            currency: strtoupper($order->currency ?? 'CLP'),
            idempotencyKey: $idempotencyKey,
            rawPayload: $request->all(),
        );
    }
}
