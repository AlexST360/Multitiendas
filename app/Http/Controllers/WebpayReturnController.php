<?php

namespace App\Http\Controllers;

use App\Domain\Payments\ConfirmPaymentService;
use App\Domain\Payments\Gateways\GatewayFactory;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebpayReturnController extends Controller
{
    public function handle(
        Request $request,
        GatewayFactory $factory,
        ConfirmPaymentService $service,
    ): RedirectResponse {

        $tokenWs = $request->input('token_ws');

        /*
        |--------------------------------------------------------------------------
        | Caso 1: Usuario canceló o el pago expiró
        |--------------------------------------------------------------------------
        |
        | Transbank no envía token_ws en cancellation/timeout.
        | Envía TBK_TOKEN + TBK_ORDEN_COMPRA + TBK_ID_SESION.
        | Usamos TBK_ORDEN_COMPRA (nuestro buy_order) para recuperar la orden.
        |
        */

        if (! $tokenWs) {
            $buyOrder = $request->input('TBK_ORDEN_COMPRA');

            if ($buyOrder && str_contains($buyOrder, '-')) {
                [$storeId, $orderId] = explode('-', $buyOrder, 2);

                $store = Store::find((int) $storeId);
                $order = $store
                    ? Order::where('store_id', $store->id)->where('id', (int) $orderId)->first()
                    : null;

                if ($store && $order) {
                    return redirect()
                        ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
                        ->with('error', 'El pago fue cancelado o expiró.');
                }
            }

            return redirect()->route('home')->with('error', 'El pago fue cancelado.');
        }

        /*
        |--------------------------------------------------------------------------
        | Caso 2: Flujo normal — commit + confirmar pago
        |--------------------------------------------------------------------------
        |
        | El buy_order viene en la respuesta del commit.
        | Formato: "{store_id}-{order_id}" (definido en WebpayInitController).
        |
        */

        // Commit devuelve buy_order que usamos para encontrar store + order
        // Necesitamos hacerlo en dos pasos porque commit es la fuente de buy_order.
        // Hacemos el commit aquí, luego resolvemos store/order, luego confirmamos.

        try {
            // Resolver store desde caché (guardada en WebpayInitController)
            $storeIdFromCache = Cache::get('webpay_token_' . $tokenWs);
            $storeForCommit   = $storeIdFromCache ? Store::find((int) $storeIdFromCache) : null;

            // Construir gateway con credenciales de la tienda (o fallback a env si no hay config)
            $gateway     = $factory->webpay($storeForCommit ?? new Store());
            $rawResponse = $gateway->tx->commit($tokenWs);

            if (! str_contains((string) $rawResponse->getBuyOrder(), '-')) {
                throw new \RuntimeException('buy_order inválido en la respuesta de Transbank.');
            }

            [$storeId, $orderId] = explode('-', $rawResponse->getBuyOrder(), 2);

            $store = Store::findOrFail((int) $storeId);
            $order = Order::where('store_id', $store->id)
                ->where('id', (int) $orderId)
                ->firstOrFail();

        } catch (\Exception $e) {
            return redirect()->route('home')
                ->with('error', 'Error al procesar el pago con Webpay: ' . $e->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | Paso b: construir DTO y confirmar en nuestro sistema
        |--------------------------------------------------------------------------
        |
        | Usamos la respuesta ya obtenida del commit para armar el DTO.
        | No llamamos a commit() de nuevo (solo se puede llamar una vez).
        |
        */

        if ($rawResponse->getResponseCode() !== 0) {
            return redirect()
                ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
                ->with('error', 'Pago rechazado por Transbank. Código: ' . $rawResponse->getResponseCode());
        }

        $dto = new \App\Domain\Payments\DTO\ConfirmedPayment(
            storeId: (int) $store->id,
            orderId: (int) $order->id,
            gateway: $gateway->name(),
            gatewayReference: $rawResponse->getBuyOrder(),
            amount: (int) $rawResponse->getAmount(),
            currency: 'CLP',
            idempotencyKey: $tokenWs,
            rawPayload: [
                'authorization_code' => $rawResponse->getAuthorizationCode(),
                'transaction_date'   => $rawResponse->getTransactionDate(),
                'vci'                => $rawResponse->getVci(),
                'card_detail'        => (array) $rawResponse->getCardDetail(),
                'response_code'      => $rawResponse->getResponseCode(),
                'status'             => $rawResponse->getStatus(),
            ],
        );

        try {
            $service->confirmDto($dto);
        } catch (\DomainException $e) {
            return redirect()
                ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
            ->with('status', 'Pago confirmado con Webpay');
    }
}
