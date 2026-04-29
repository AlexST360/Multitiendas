<x-storefront-layout>
    <div class="max-w-6xl mx-auto py-10 px-4">

        {{-- HERO --}}
        <div class="rounded-lg border bg-white p-8 mb-10">
            <div class="text-sm text-gray-500">Bienvenido a</div>
            <h1 class="text-4xl font-semibold mt-1">{{ $store->name }}</h1>

            <p class="text-gray-600 mt-3 max-w-2xl">
                Descubre nuestros productos seleccionados.
                Compra segura y envío rápido.
            </p>

            <div class="mt-6">
                <a href="#productos"
                   class="px-4 py-2 bg-black text-white rounded-md">
                    Ver productos
                </a>
            </div>
        </div>

        {{-- TÍTULO --}}
        <div id="productos" class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Productos</h2>
            <div class="text-sm text-gray-500">
                {{ $products->total() }} disponibles
            </div>
        </div>

        @if($products->isEmpty())
            <div class="rounded-lg border bg-white p-8 text-gray-600">
                Aún no hay productos activos en esta tienda.
            </div>
        @else

            {{-- GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                @foreach($products as $product)
                    <a href="{{ route('storefront.product.show', [$store, $product]) }}"
                       class="group rounded-lg border bg-white overflow-hidden hover:shadow-sm transition">

                        {{-- Imagen --}}
                        <div class="aspect-[4/3] bg-gray-50">
                            @if($product->primaryImage)
                                <img
                                    src="{{ asset('storage/' . $product->primaryImage->path) }}"
                                    class="w-full h-full object-cover"
                                    alt=""
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    Sin imagen
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold group-hover:underline">
                                        {{ $product->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        Stock: {{ $product->stock }}
                                    </div>
                                </div>

                                <div class="font-semibold">
                                    ${{ number_format($product->price) }}
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="inline-flex px-3 py-2 bg-black text-white text-sm rounded-md">
                                    Ver detalle
                                </div>
                            </div>
                        </div>

                    </a>
                @endforeach

            </div>

            {{-- Paginación --}}
            <div class="mt-8">
                {{ $products->links() }}
            </div>

        @endif

    </div>
</x-storefront-layout>