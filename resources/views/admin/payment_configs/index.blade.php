<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Métodos de pago</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">

        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-4">

            {{-- Webpay --}}
            @php $webpay = $configs->get('webpay') @endphp
            <div class="bg-white rounded-lg border p-5 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-gray-800">Webpay Plus <span class="text-xs text-gray-500">(Transbank)</span></div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        @if($webpay)
                            Entorno: {{ $webpay->environment }}
                            &middot;
                            @if($webpay->active)
                                <span class="text-green-700 font-medium">Activo</span>
                            @else
                                <span class="text-gray-400">Inactivo</span>
                            @endif
                        @else
                            Sin configurar — usando credenciales de .env
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.payment_configs.edit', 'webpay') }}"
                   class="px-4 py-2 bg-black text-white text-sm rounded hover:bg-gray-800">
                    Configurar
                </a>
            </div>

            {{-- MercadoPago --}}
            @php $mp = $configs->get('mercadopago') @endphp
            <div class="bg-white rounded-lg border p-5 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-gray-800">MercadoPago <span class="text-xs text-gray-500">(Checkout Pro)</span></div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        @if($mp)
                            Entorno: {{ $mp->environment }}
                            &middot;
                            @if($mp->active)
                                <span class="text-green-700 font-medium">Activo</span>
                            @else
                                <span class="text-gray-400">Inactivo</span>
                            @endif
                        @else
                            Sin configurar — usando credenciales de .env
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.payment_configs.edit', 'mercadopago') }}"
                   class="px-4 py-2 bg-black text-white text-sm rounded hover:bg-gray-800">
                    Configurar
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
