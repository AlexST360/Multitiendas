<?php

namespace App\Http\Controllers;

use App\Domain\Payments\ConfirmPaymentService;
use App\Domain\Payments\Gateways\MercadoPagoGateway;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recibe notificaciones server-to-server de MercadoPago (IPN / webhooks).
 *
 * MP envía POST con body: {"type": "payment", "data": {"id": 123456789}}
 *
 * El controller:
 *   1. Ignora tipos que no sean 'payment'
 *   2. Llama la API de MP para obtener detalles del payment (incluyendo external_reference)
 *   3. Resuelve store + order desde external_reference ("{store_id}-{order_id}")
 *   4. Confirma el pago (idempotente — el return URL puede haber llegado antes)
 *
 * Siempre retorna HTTP 200 para que MP no reintente. Los errores se loguean.
 */
class MercadoPagoWebhookController extends Controller
{
    public function handle(
        Request $request,
        MercadoPagoGateway $gateway,
        ConfirmPaymentService $service,
    ): JsonResponse {

        $type      = $request->input('type');
        $paymentId = $request->input('data.id');

        /*
        |--------------------------------------------------------------------------
        | Ignorar notificaciones que no sean de pagos
        |--------------------------------------------------------------------------
        |
        | MP envía otros tipos como 'merchant_order', 'point_integration_wh', etc.
        | Solo nos interesa 'payment'.
        |
        */

        if ($type !== 'payment' || ! $paymentId) {
            return response()->json(['ok' => true, 'skipped' => true]);
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener detalles del payment para resolver store + order
        |--------------------------------------------------------------------------
        */

        try {
            $payment = $gateway->payClient->get((int) $paymentId);
        } catch (\Exception $e) {
            // Loguear y retornar 200 para que MP no reintente
            \Log::error('MercadoPago webhook: error al obtener payment', [
                'payment_id' => $paymentId,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'error' => 'No se pudo obtener el payment de MP.']);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolver store + order desde external_reference
        |--------------------------------------------------------------------------
        */

        $externalRef = $payment->external_reference ?? '';

        if (! str_contains($externalRef, '-')) {
            return response()->json(['ok' => false, 'error' => 'external_reference inválido.']);
        }

        [$storeId, $orderId] = explode('-', $externalRef, 2);

        $store = Store::find((int) $storeId);
        $order = $store
            ? Order::where('store_id', $store->id)->where('id', (int) $orderId)->first()
            : null;

        if (! $store || ! $order) {
            return response()->json(['ok' => false, 'error' => 'Orden no encontrada.']);
        }

        /*
        |--------------------------------------------------------------------------
        | Confirmar pago (idempotente)
        |--------------------------------------------------------------------------
        */

        try {
            $dto     = $gateway->parseWebhook($request, $store, $order);
            $payment = $service->confirmDto($dto);
        } catch (\DomainException $e) {
            // 409 ya pagada — idempotencia OK (return URL llegó antes)
            return response()->json(['ok' => true, 'idempotent' => true]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'ok'             => true,
            'payment_id'     => $payment->id,
            'payment_status' => $payment->status->value,
        ]);
    }
}
