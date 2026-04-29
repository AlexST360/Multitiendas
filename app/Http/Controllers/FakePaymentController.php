<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\ConfirmPaymentService;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FakePaymentController extends Controller
{
    public function confirm(Request $request, Store $store, string $token, ConfirmPaymentService $service): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'staging']), 404);

        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($order->status === OrderStatus::PendingPayment, 409);

        $idempotencyKey = $request->header('Idempotency-Key')
            ?? $request->input('idempotency_key')
            ?? ('fake_' . $store->id . '_' . $order->public_token);

        $service->confirm([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'gateway' => 'fake',
            'gateway_reference' => 'fake_' . Str::uuid()->toString(),
            'amount' => (int) $order->total,
            'currency' => $order->currency ?? 'CLP',
            'idempotency_key' => $idempotencyKey,
            'raw_payload' => [
                'source' => 'thankyou_button',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return redirect()
            ->route('storefront.order.thankyou', [$store->slug, $token])
            ->with('status', 'Pago simulado confirmado');
    }
}