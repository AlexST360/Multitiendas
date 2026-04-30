<?php

namespace App\Domain\Payments\Gateways;

use App\Models\Store;
use App\Models\StorePaymentConfig;

class GatewayFactory
{
    public function webpay(Store $store): WebpayGateway
    {
        $config = $this->loadConfig($store, 'webpay');

        return new WebpayGateway($config);
    }

    public function mercadopago(Store $store): MercadoPagoGateway
    {
        $config = $this->loadConfig($store, 'mercadopago');

        return new MercadoPagoGateway($config);
    }

    /*
    |--------------------------------------------------------------------------
    | Busca config en DB; si no existe devuelve [] (gateways usan .env fallback)
    |--------------------------------------------------------------------------
    */
    private function loadConfig(Store $store, string $gateway): array
    {
        $record = StorePaymentConfig::query()
            ->where('store_id', $store->id)
            ->where('gateway', $gateway)
            ->where('active', true)
            ->first();

        if (! $record) {
            return [];
        }

        return array_merge(
            ['environment' => $record->environment],
            $record->credentials ?? []
        );
    }
}
