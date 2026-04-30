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

            {{-- Estado del envío --}}
            @if ($order->shipment)
                @php
                    $s = $order->shipment;
                    $colors = [
                        'pending'   => 'bg-gray-100 text-gray-700',
                        'preparing' => 'bg-yellow-100 text-yellow-800',
                        'shipped'   => 'bg-blue-100 text-blue-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'returned'  => 'bg-red-100 text-red-800',
                    ];
                    $color = $colors[$s->status->value] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <div class="mt-6 rounded-lg border p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Envío</h2>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ $s->status->label() }}
                        </span>
                    </div>
                    <div class="mt-3 text-sm text-gray-700 space-y-1">
                        @if($s->carrier)
                            <div><span class="text-gray-500">Courier:</span> {{ $s->carrier }}</div>
                        @endif
                        @if($s->tracking_number)
                            <div>
                                <span class="text-gray-500">Seguimiento:</span>
                                @if($s->tracking_url)
                                    <a href="{{ $s->tracking_url }}" target="_blank"
                                       class="font-mono text-blue-600 underline">{{ $s->tracking_number }}</a>
                                @else
                                    <span class="font-mono">{{ $s->tracking_number }}</span>
                                @endif
                            </div>
                        @endif
                        @if($s->shipped_at)
                            <div><span class="text-gray-500">Despachado:</span> {{ $s->shipped_at->format('d/m/Y') }}</div>
                        @endif
                        @if($s->delivered_at)
                            <div><span class="text-gray-500">Entregado:</span> {{ $s->delivered_at->format('d/m/Y') }}</div>
                        @endif
                    </div>
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

                @if($order->discount > 0)
                    <div class="mt-3 flex justify-between text-sm text-green-700">
                        <span>
                            Descuento
                            @if($order->coupon_code)
                                <span class="font-mono">({{ $order->coupon_code }})</span>
                            @endif
                        </span>
                        <span>-${{ number_format($order->discount) }}</span>
                    </div>
                @endif

                <div class="mt-4 flex justify-between font-semibold">
                    <span>Total</span>
                    <span>${{ number_format($order->total) }}</span>
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <a class="underline text-sm" href="{{ route('storefront.store.index', $store) }}">
                    Volver a la tienda
                </a>
                @if($order->status->value === 'paid')
                    <a href="{{ route('storefront.order.boleta', [$store->slug, $order->public_token]) }}"
                       class="px-4 py-2 border border-gray-300 rounded text-sm hover:bg-gray-50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Descargar boleta PDF
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-storefront-layout>