<x-storefront-layout>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="rounded-lg border bg-white p-8">
            <div class="text-sm text-gray-500">{{ $store->name }}</div>
            <h1 class="text-3xl font-semibold mt-1">Orden creada</h1>

            @if (session('status'))
                <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mt-4 text-gray-700">
                Estado: <span class="font-semibold">{{ $order->status->value }}</span>
            </div>

            {{-- Botones de pago --}}
            @if ($order->status->value === 'pending_payment')
                <div class="mt-6 flex flex-col sm:flex-row gap-3">

                    {{-- Webpay Plus --}}
                    <form method="POST"
                          action="{{ route('payments.webpay.init', [$store->slug, $order->public_token ?? $token]) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 bg-[#1a1a5e] text-white rounded-md hover:bg-[#12124a]">
                            Pagar con Webpay
                        </button>
                    </form>

                    {{-- MercadoPago Checkout Pro --}}
                    <form method="POST"
                          action="{{ route('payments.mp.init', [$store->slug, $order->public_token ?? $token]) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 bg-[#009ee3] text-white rounded-md hover:bg-[#0082c0]">
                            Pagar con MercadoPago
                        </button>
                    </form>

                </div>
            @endif

            {{-- Botón de pago fake (solo en local/staging) --}}
            @if ($order->status->value === 'pending_payment' && app()->environment(['local', 'staging']))
                <div class="mt-3">
                    <form method="POST"
                          action="{{ route('storefront.order.confirmFakePayment', [$store->slug, $order->public_token ?? $token]) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 bg-black text-white rounded-md hover:bg-gray-900">
                            Simular pago
                        </button>
                        <p class="mt-2 text-xs text-gray-500">
                            Disponible solo en entorno local/staging.
                        </p>
                    </form>
                </div>
            @endif

            {{-- Mensaje de error (cancelación, rechazo) --}}
            @if (session('error'))
                <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-6">
                <h2 class="font-semibold mb-2">Detalle</h2>

                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex justify-between border-b pb-2">
                            <div>
                                <div class="font-semibold">{{ $item->name }}</div>
                                <div class="text-sm text-gray-500">
                                    ${{ number_format($item->unit_price) }} x {{ $item->qty }}
                                </div>
                            </div>
                            <div class="font-semibold">
                                ${{ number_format($item->line_total) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-between font-semibold">
                    <span>Total</span>
                    <span>${{ number_format($order->total) }}</span>
                </div>
            </div>

            <div class="mt-8">
                <a class="underline" href="{{ route('storefront.store.index', $store) }}">
                    Volver a la tienda
                </a>
            </div>
        </div>
    </div>
</x-storefront-layout>