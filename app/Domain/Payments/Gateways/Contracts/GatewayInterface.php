<?php

namespace App\Domain\Payments\Gateways\Contracts;

use App\Domain\Payments\DTO\ConfirmedPayment;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;

interface GatewayInterface
{
    /**
     * Nombre único del gateway (e.g., 'fake', 'webpay', 'mercadopago').
     * Se persiste en la tabla payments.gateway.
     */
    public function name(): string;

    /**
     * Parsea la notificación entrante (webhook o return URL) y retorna
     * un DTO listo para pasar a ConfirmPaymentService::confirmDto().
     *
     * Cada gateway implementa su propia lógica de validación y extracción
     * de datos desde el request (firma HMAC, llamada a API de confirmación, etc.).
     *
     * @throws \InvalidArgumentException  Si el payload es inválido o no corresponde a esta orden.
     * @throws \RuntimeException          Si falla la comunicación con la API del gateway.
     */
    public function parseWebhook(Request $request, Store $store, Order $order): ConfirmedPayment;
}
