<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Gateways\WebpayGateway;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebpayInitController extends Controller
{
    public function handle(
        Request $request,
        Store $store,
        string $token,
        WebpayGateway $gateway,
    ): RedirectResponse {

        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($order->status === OrderStatus::PendingPayment, 409, 'La orden no está pendiente de pago.');

        $returnUrl = route('payments.webpay.return');

        $redirectUrl = $gateway->createTransaction($order, $store, $returnUrl);

        return redirect($redirectUrl);
    }
}
