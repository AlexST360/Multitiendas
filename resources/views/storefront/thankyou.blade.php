<x-guest-layout>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="rounded-lg border bg-white p-8">
            <div class="text-sm text-gray-500">{{ $store->name }}</div>
            <h1 class="text-3xl font-semibold mt-1">Orden creada</h1>

            <div class="mt-4 text-gray-700">
                Estado: <span class="font-semibold">{{ $order->status->value }}</span>
            </div>

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
</x-guest-layout>