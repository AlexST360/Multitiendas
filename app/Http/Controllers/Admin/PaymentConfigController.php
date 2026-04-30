<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorePaymentConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentConfigController extends Controller
{
    private const GATEWAYS = ['webpay', 'mercadopago'];

    public function index(): View
    {
        $storeId = auth()->user()->store_id;

        $configs = StorePaymentConfig::query()
            ->where('store_id', $storeId)
            ->get()
            ->keyBy('gateway');

        return view('admin.payment_configs.index', compact('configs'));
    }

    public function edit(string $gateway): View
    {
        abort_unless(in_array($gateway, self::GATEWAYS), 404);

        $storeId = auth()->user()->store_id;

        $config = StorePaymentConfig::firstOrNew(
            ['store_id' => $storeId, 'gateway' => $gateway],
            ['environment' => 'sandbox', 'credentials' => [], 'active' => false],
        );

        return view('admin.payment_configs.edit', compact('config', 'gateway'));
    }

    public function update(Request $request, string $gateway): RedirectResponse
    {
        abort_unless(in_array($gateway, self::GATEWAYS), 404);

        $storeId = auth()->user()->store_id;

        $environment = $request->input('environment', 'sandbox');
        $active      = $request->boolean('active');

        /*
        |--------------------------------------------------------------------------
        | Credenciales por gateway — solo guardar las que vienen en el form
        |--------------------------------------------------------------------------
        */

        $credentials = match ($gateway) {
            'webpay'       => array_filter([
                'commerce_code' => $request->input('commerce_code'),
                'api_key'       => $request->input('api_key'),
            ]),
            'mercadopago'  => array_filter([
                'access_token'  => $request->input('access_token'),
                'public_key'    => $request->input('public_key'),
            ]),
            default        => [],
        };

        StorePaymentConfig::updateOrCreate(
            ['store_id' => $storeId, 'gateway' => $gateway],
            [
                'environment' => $environment,
                'credentials' => $credentials ?: null,
                'active'      => $active,
            ],
        );

        return redirect()
            ->route('admin.payment_configs.index')
            ->with('status', 'Configuración de ' . $gateway . ' guardada.');
    }
}
