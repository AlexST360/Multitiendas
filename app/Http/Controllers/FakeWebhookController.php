<?php

namespace App\Http\Controllers;

use App\Domain\Payments\ConfirmPaymentService;
use App\Domain\Payments\Gateways\FakeGateway;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FakeWebhookController extends Controller
{
    public function handle(
        Request $request,
        FakeGateway $gateway,
        ConfirmPaymentService $service,
    ): JsonResponse {
        $data = $request->validate([
            'store_slug'        => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'order_token'       => ['required', 'string', 'max:255'],
            'amount'            => ['required', 'integer', 'min:1'],
            'currency'          => ['required', 'string', 'size:3'],
            'idempotency_key'   => ['required', 'string', 'max:255'],
            'gateway_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $store = Store::query()
            ->where('slug', $data['store_slug'])
            ->firstOrFail();

        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $data['order_token'])
            ->firstOrFail();

        try {
            $dto     = $gateway->parseWebhook($request, $store, $order);
            $payment = $service->confirmDto($dto);
        } catch (\DomainException $e) {
            return response()->json([
                'ok'           => false,
                'error'        => $e->getMessage(),
                'order_status' => $order->refresh()->status->value,
            ], 409);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'ok'           => false,
                'error'        => $e->getMessage(),
                'order_status' => $order->refresh()->status->value,
            ], 422);
        }

        return response()->json([
            'ok'             => true,
            'order_status'   => $order->refresh()->status->value,
            'payment_id'     => $payment->id,
            'payment_status' => $payment->status->value,
        ]);
    }
}
