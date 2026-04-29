<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\DTO\ConfirmedPayment;
use App\Domain\Payments\Gateways\Contracts\GatewayInterface;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Transbank\Webpay\WebpayPlus;
use Transbank\Webpay\WebpayPlus\Transaction;

/**
 * Webpay Plus — Transbank Chile.
 *
 * Documentación: https://www.transbankdevelopers.cl/documentacion/webpay-plus
 *
 * Flujo completo:
 *   1. WebpayInitController::handle()
 *      └─ WebpayGateway::createTransaction() → redirect URL → usuario va a Transbank
 *   2. Usuario paga (o cancela) en Transbank
 *   3. Transbank hace POST a /payments/webpay/return
 *      └─ WebpayReturnController::handle()
 *         └─ WebpayGateway::commit() → ConfirmedPayment → ConfirmPaymentService
 *
 * buy_order format: "{store_id}-{order_id}" (máx 26 chars Transbank).
 *
 * SDK v5: ya no existe configureForIntegration() en WebpayPlus.
 * Ahora se usan Transaction::buildForIntegration() / buildForProduction().
 */
class WebpayGateway implements GatewayInterface
{
    public Transaction $tx;

    public function __construct()
    {
        $env          = config('services.transbank.environment', 'integration');
        $commerceCode = config('services.transbank.commerce_code', WebpayPlus::INTEGRATION_COMMERCE_CODE);
        $apiKey       = config('services.transbank.api_key', \Transbank\Webpay\Options::INTEGRATION_API_KEY);

        $this->tx = $env === 'production'
            ? Transaction::buildForProduction($apiKey, $commerceCode)
            : Transaction::buildForIntegration($apiKey, $commerceCode);
    }

    public function name(): string
    {
        return 'webpay';
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Iniciar transacción → obtener URL de redirección a Transbank
    |--------------------------------------------------------------------------
    |
    | Devuelve la URL completa con token_ws para redirigir al usuario.
    | El buy_order codifica store_id + order_id para recuperarlos en el return.
    |
    */

    public function createTransaction(Order $order, Store $store, string $returnUrl): string
    {
        $buyOrder  = $store->id . '-' . $order->id;                 // e.g. "1-42"
        $sessionId = 'sess_' . $order->id . '_' . time();
        $amount    = (int) $order->total;

        $response = $this->tx->create($buyOrder, $sessionId, $amount, $returnUrl);

        return $response->getUrl() . '?token_ws=' . $response->getToken();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Confirmar transacción (commit) → ConfirmedPayment DTO
    |--------------------------------------------------------------------------
    |
    | Llamado desde WebpayReturnController cuando Transbank redirige al usuario.
    | Realiza el commit (único — si ya fue llamado, Transbank devuelve error).
    | Construye y retorna el DTO para pasarlo a ConfirmPaymentService.
    |
    | @throws \InvalidArgumentException  Si el pago fue rechazado por Transbank.
    | @throws \RuntimeException          Si la comunicación con Transbank falla.
    |
    */

    public function commit(string $tokenWs, Store $store, Order $order): ConfirmedPayment
    {
        $response = $this->tx->commit($tokenWs);

        if ($response->getResponseCode() !== 0) {
            throw new \InvalidArgumentException(
                'Pago rechazado por Transbank. Código: ' . $response->getResponseCode()
            );
        }

        return new ConfirmedPayment(
            storeId: (int) $store->id,
            orderId: (int) $order->id,
            gateway: $this->name(),
            gatewayReference: $response->getBuyOrder(),
            amount: (int) $response->getAmount(),
            currency: 'CLP',
            idempotencyKey: $tokenWs,                               // token_ws es único por transacción
            rawPayload: [
                'authorization_code' => $response->getAuthorizationCode(),
                'transaction_date'   => $response->getTransactionDate(),
                'vci'                => $response->getVci(),
                'card_detail'        => (array) $response->getCardDetail(),
                'response_code'      => $response->getResponseCode(),
                'status'             => $response->getStatus(),
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | parseWebhook — requerido por GatewayInterface (no aplica a Webpay Plus)
    |--------------------------------------------------------------------------
    |
    | Webpay Plus usa redirect + return URL, no webhooks server-to-server.
    | El flujo de confirmación va por commit() → WebpayReturnController.
    |
    */

    public function parseWebhook(Request $request, Store $store, Order $order): ConfirmedPayment
    {
        throw new \LogicException(
            'Webpay Plus no usa webhooks. El flujo de retorno pasa por WebpayReturnController::handle().'
        );
    }
}
