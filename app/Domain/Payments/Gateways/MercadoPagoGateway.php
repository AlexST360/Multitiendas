<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\DTO\ConfirmedPayment;
use App\Domain\Payments\Gateways\Contracts\GatewayInterface;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

/**
 * MercadoPago — Checkout Pro.
 *
 * Documentación: https://www.mercadopago.cl/developers/es/docs/checkout-pro/landing
 * SDK PHP:       mercadopago/dx-php v3
 *
 * Flujo completo:
 *   1. MercadoPagoInitController::handle()
 *      └─ MercadoPagoGateway::createPreference() → init_point → usuario va a MP
 *   2. Usuario paga en MercadoPago
 *   3a. MP redirige al usuario (GET) a back_url según resultado
 *       └─ MercadoPagoReturnController::handle() → confirma pago
 *   3b. MP envía webhook (POST) a notification_url con payment.id
 *       └─ MercadoPagoWebhookController::handle() → confirma pago (idempotente)
 *
 * external_reference format: "{store_id}-{order_id}" (para recuperar datos en return/webhook).
 */
class MercadoPagoGateway implements GatewayInterface
{
    public PaymentClient    $payClient;
    public PreferenceClient $prefClient;

    private bool $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('services.mercadopago.environment', 'sandbox') !== 'production';

        MercadoPagoConfig::setAccessToken(
            config('services.mercadopago.access_token')
        );

        $this->payClient  = new PaymentClient();
        $this->prefClient = new PreferenceClient();
    }

    public function name(): string
    {
        return 'mercadopago';
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Crear preference → obtener URL de Checkout Pro
    |--------------------------------------------------------------------------
    |
    | Retorna sandbox_init_point (sandbox) o init_point (producción).
    | external_reference codifica store_id + order_id para recuperarlos en
    | el return URL y el webhook.
    |
    */

    public function createPreference(
        Order $order,
        Store $store,
        array $backUrls,
        string $notificationUrl
    ): string {
        $preference = $this->prefClient->create([
            'items' => [[
                'title'       => 'Orden #' . $order->id . ' — ' . $store->name,
                'quantity'    => 1,
                'currency_id' => 'CLP',
                'unit_price'  => (float) $order->total,
            ]],
            'back_urls' => [
                'success' => $backUrls['success'],
                'failure' => $backUrls['failure'],
                'pending' => $backUrls['pending'],
            ],
            'auto_return'        => 'approved',
            'external_reference' => $store->id . '-' . $order->id,
            'notification_url'   => $notificationUrl,
        ]);

        return $this->isSandbox
            ? $preference->sandbox_init_point
            : $preference->init_point;
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Parsear webhook / return URL → ConfirmedPayment DTO
    |--------------------------------------------------------------------------
    |
    | MP envía el payment_id en el webhook (data.id) y en el return URL
    | (query param payment_id). Se verifica contra la API de MP.
    |
    | @throws \InvalidArgumentException  Si el pago no está aprobado.
    | @throws \RuntimeException          Si la comunicación con la API de MP falla.
    |
    */

    public function parseWebhook(Request $request, Store $store, Order $order): ConfirmedPayment
    {
        // Soporta tanto webhook (data.id) como return URL (payment_id en query)
        $paymentId = $request->input('data.id') ?? $request->query('payment_id');

        if (! $paymentId) {
            throw new \InvalidArgumentException('No se recibió payment_id de MercadoPago.');
        }

        $payment = $this->payClient->get((int) $paymentId);

        if ($payment->status !== 'approved') {
            throw new \InvalidArgumentException(
                'Pago MP no aprobado. Estado: ' . $payment->status
            );
        }

        $idempotencyKey = $request->header('X-Request-Id') ?? ('mp_' . $paymentId);

        return new ConfirmedPayment(
            storeId:          (int) $store->id,
            orderId:          (int) $order->id,
            gateway:          $this->name(),
            gatewayReference: (string) $payment->id,
            amount:           (int) $payment->transaction_amount,
            currency:         strtoupper($payment->currency_id),
            idempotencyKey:   $idempotencyKey,
            rawPayload:       [
                'status'              => $payment->status,
                'status_detail'       => $payment->status_detail,
                'payment_method_id'   => $payment->payment_method_id,
                'payment_type_id'     => $payment->payment_type_id,
                'external_reference'  => $payment->external_reference,
                'date_approved'       => $payment->date_approved,
                'payer_email'         => $payment->payer->email ?? null,
            ],
        );
    }
}
