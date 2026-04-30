<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Gateways\GatewayFactory;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MercadoPagoInitController extends Controller
{
    public function handle(
        Request $request,
        Store $store,
        string $token,
        GatewayFactory $factory,
    ): RedirectResponse {

        $gateway = $factory->mercadopago($store);

        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($order->status === OrderStatus::PendingPayment, 409, 'La orden no está pendiente de pago.');

        $backUrls = [
            'success' => route('payments.mp.return', ['result' => 'success', 'store' => $store->slug, 'token' => $order->public_token]),
            'failure' => route('payments.mp.return', ['result' => 'failure', 'store' => $store->slug, 'token' => $order->public_token]),
            'pending' => route('payments.mp.return', ['result' => 'pending', 'store' => $store->slug, 'token' => $order->public_token]),
        ];

        $notificationUrl = route('payments.mp.webhook');

        $initPoint = $gateway->createPreference($order, $store, $backUrls, $notificationUrl);

        return redirect($initPoint);
    }
}
