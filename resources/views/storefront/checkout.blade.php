<x-storefront-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">

        <div class="mb-6">
            <div class="text-sm text-gray-500">{{ $store->name }}</div>
            <h1 class="text-3xl font-semibold">Checkout</h1>
        </div>

        @if(session('status'))
            <div class="mb-4 text-green-600 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if(empty($items))
            <div class="rounded-lg border bg-white p-8 text-gray-600">
                Tu carrito está vacío.
                <div class="mt-4">
                    <a class="underline" href="{{ route('storefront.store.index', $store) }}">
                        Volver a la tienda
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Items --}}
                <div class="md:col-span-2 rounded-lg border bg-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Tu carrito</h2>

                        <button form="cart-update-form"
                                class="px-4 py-2 border rounded-md text-sm"
                                type="submit">
                            Actualizar
                        </button>
                    </div>

                    <form id="cart-update-form" method="POST" action="{{ route('storefront.cart.update', $store) }}" class="space-y-4">
                        @csrf

                        @foreach($items as $item)
                            <div class="flex gap-4 border-b pb-4">
                                <div class="w-20 h-20 bg-gray-50 rounded border overflow-hidden">
                                    @if($item['image_path'])
                                        <img src="{{ asset('storage/' . $item['image_path']) }}"
                                             class="w-full h-full object-cover"
                                             alt="">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                                            Sin imagen
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <div class="font-semibold">{{ $item['name'] }}</div>
                                    <div class="text-sm text-gray-500">
                                        ${{ number_format($item['price']) }}
                                    </div>

                                    <div class="mt-2 flex items-center gap-3">
                                        <label class="text-sm text-gray-600">Cantidad</label>

                                        <input
                                            type="number"
                                            name="qty[{{ $item['product_id'] }}]"
                                            value="{{ $item['qty'] }}"
                                            min="0"
                                            class="w-24 border-gray-300 rounded"
                                        >

                                        <button
                                            form="remove-{{ $item['product_id'] }}"
                                            type="submit"
                                            class="text-red-600 underline text-sm">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <div class="font-semibold whitespace-nowrap">
                                    ${{ number_format($item['line_total']) }}
                                </div>
                            </div>
                        @endforeach
                    </form>

                    {{-- Forms de eliminar (fuera del form principal, para evitar anidados) --}}
                    @foreach($items as $item)
                        <form id="remove-{{ $item['product_id'] }}"
                              method="POST"
                              action="{{ route('storefront.cart.remove', [$store, $item['product_id']]) }}">
                            @csrf
                        </form>
                    @endforeach
                </div>

                {{-- Resumen + datos --}}
                <div class="rounded-lg border bg-white p-6">
                    <h2 class="text-lg font-semibold mb-4">Resumen</h2>

                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>${{ number_format($subtotal) }}</span>
                    </div>

                    <div class="mt-4 border-t pt-4 flex justify-between font-semibold">
                        <span>Total</span>
                        <span>${{ number_format($subtotal) }}</span>
                    </div>

                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Datos</h3>

                        {{-- Crear Orden (pending_payment) --}}
                        <form method="POST" action="{{ route('storefront.checkout.place', $store) }}" class="mt-3">
                            @csrf

                            <div class="space-y-3">
                                <input
                                    name="customer_name"
                                    class="w-full border-gray-300 rounded"
                                    placeholder="Nombre"
                                    required
                                >
                                <input
                                    name="customer_email"
                                    type="email"
                                    class="w-full border-gray-300 rounded"
                                    placeholder="Email"
                                    required
                                >
                                <input
                                    name="customer_phone"
                                    class="w-full border-gray-300 rounded"
                                    placeholder="Teléfono"
                                >
                            </div>

                            <button type="submit" class="mt-6 w-full px-4 py-2 bg-black text-white rounded-md">
                                Continuar al pago
                            </button>

                            <div class="mt-3 text-xs text-gray-400">
                                (Por ahora: crea Orden en pending_payment y muestra confirmación)
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        @endif

    </div>
</x-storefront-layout>