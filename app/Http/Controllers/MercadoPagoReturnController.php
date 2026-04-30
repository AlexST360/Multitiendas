<?php

namespace App\Http\Controllers;

use App\Domain\Payments\ConfirmPaymentService;
use App\Domain\Payments\Gateways\GatewayFactory;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Maneja el redirect de vuelta desde MercadoPago Checkout Pro.
 *
 * MP redirige al usuario con GET incluyendo:
 *   ?payment_id=...&status=approved&external_reference={store_id}-{order_id}&...
 *
 * Para pagos aprobados confirma el pago (idempotente — el webhook puede llegar antes).
 * Para rechazados/pendientes, solo redirige con mensaje informativo.
 */
class MercadoPagoReturnController extends Controller
{
    public function handle(
        Request $request,
        GatewayFactory $factory,
        ConfirmPaymentService $service,
    ): RedirectResponse {

        $result   = $request->query('result');          // success | failure | pending
        $storeSlug = $request->query('store');
        $token    = $request->query('token');

        /*
        |--------------------------------------------------------------------------
        | Resolver store y order desde query params de nuestra back_url
        |--------------------------------------------------------------------------
        */

        $store = $storeSlug ? Store::where('slug', $storeSlug)->first() : null;
        $order = ($store && $token)
            ? Order::where('store_id', $store->id)->where('public_token', $token)->first()
            : null;

        if (! $store || ! $order) {
            return redirect()->route('home')->with('error', 'No se encontró la orden.');
        }

        $gateway = $factory->mercadopago($store);

        /*
        |--------------------------------------------------------------------------
        | Caso: pago rechazado o pendiente — solo informar, no confirmar
        |--------------------------------------------------------------------------
        */

        if ($result !== 'success') {
            $message = $result === 'pending'
                ? 'Tu pago está pendiente de confirmación por MercadoPago.'
                : 'El pago fue rechazado o cancelado.';

            return redirect()
                ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
                ->with('error', $message);
        }

        /*
        |--------------------------------------------------------------------------
        | Caso: success — confirmar pago (idempotente si el webhook llegó antes)
        |--------------------------------------------------------------------------
        */

        try {
            $dto = $gateway->parseWebhook($request, $store, $order);
            $service->confirmDto($dto);
        } catch (\DomainException $e) {
            // 409 orden ya pagada — idempotencia, webhook llegó primero, OK
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            // Si MP aún no tiene el payment disponible, el webhook confirmará
        }

        return redirect()
            ->route('storefront.order.thankyou', [$store->slug, $order->public_token])
            ->with('status', 'Pago confirmado con MercadoPago');
    }
}
