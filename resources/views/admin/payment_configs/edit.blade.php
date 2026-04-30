<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payment_configs.index') }}" class="text-gray-400 hover:text-gray-700">← Métodos de pago</a>
            <span class="text-gray-300">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if($gateway === 'webpay') Webpay Plus
                @elseif($gateway === 'mercadopago') MercadoPago
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white rounded-lg border p-6">

            <form method="POST" action="{{ route('admin.payment_configs.update', $gateway) }}">
                @csrf
                @method('PUT')

                {{-- Entorno --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Entorno</label>
                    <select name="environment"
                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-black focus:border-black">
                        <option value="sandbox"    {{ ($config->environment ?? 'sandbox') === 'sandbox'    ? 'selected' : '' }}>Sandbox (pruebas)</option>
                        <option value="production" {{ ($config->environment ?? '') === 'production' ? 'selected' : '' }}>Producción</option>
                    </select>
                </div>

                {{-- Credenciales Webpay --}}
                @if($gateway === 'webpay')
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commerce Code</label>
                        <input type="text" name="commerce_code"
                               value="{{ $config->credentials['commerce_code'] ?? '' }}"
                               placeholder="597055555532"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm font-mono focus:ring-black focus:border-black">
                        <p class="mt-1 text-xs text-gray-400">Dejar en blanco para usar el valor de .env</p>
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key (Secret)</label>
                        <input type="password" name="api_key"
                               value="{{ $config->credentials['api_key'] ?? '' }}"
                               placeholder="••••••••"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm font-mono focus:ring-black focus:border-black">
                    </div>
                @endif

                {{-- Credenciales MercadoPago --}}
                @if($gateway === 'mercadopago')
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                        <input type="password" name="access_token"
                               value="{{ $config->credentials['access_token'] ?? '' }}"
                               placeholder="APP_USR-••••••••"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm font-mono focus:ring-black focus:border-black">
                        <p class="mt-1 text-xs text-gray-400">Dejar en blanco para usar el valor de .env</p>
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Public Key</label>
                        <input type="text" name="public_key"
                               value="{{ $config->credentials['public_key'] ?? '' }}"
                               placeholder="APP_USR-••••••••"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm font-mono focus:ring-black focus:border-black">
                    </div>
                @endif

                {{-- Activo --}}
                <div class="mb-6 flex items-center gap-3">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" id="active"
                           {{ ($config->active ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-black focus:ring-black">
                    <label for="active" class="text-sm text-gray-700">
                        Usar estas credenciales (activo)
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="px-5 py-2 bg-black text-white text-sm rounded hover:bg-gray-800">
                        Guardar
                    </button>
                    <a href="{{ route('admin.payment_configs.index') }}"
                       class="px-5 py-2 border text-sm rounded text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
