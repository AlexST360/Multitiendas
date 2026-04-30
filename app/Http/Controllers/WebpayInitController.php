<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Gateways\GatewayFactory;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebpayInitController extends Controller
{
    public function handle(
        Request $request,
        Store $store,
        string $token,
        GatewayFactory $factory,
    ): RedirectResponse {

        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($order->status === OrderStatus::PendingPayment, 409, 'La orden no está pendiente de pago.');

        $gateway  = $factory->webpay($store);
        $response = $gateway->tx->create(
            $store->id . '-' . $order->id,
            'sess_' . $order->id . '_' . time(),
            (int) $order->total,
            route('payments.webpay.return')
        );

        // Guardar qué tienda inició este token para recuperarla en el return
        Cache::put('webpay_token_' . $response->getToken(), $store->id, now()->addHour());

        return redirect($response->getUrl() . '?token_ws=' . $response->getToken());
    }
}
