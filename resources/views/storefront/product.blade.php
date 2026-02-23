<x-guest-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">

        <div class="mb-6">
            <div class="text-sm text-gray-500">{{ $store->name }}</div>
            <h1 class="text-3xl font-semibold">{{ $product->name }}</h1>
        </div>

        @if(session('status'))
            <div class="mb-4 text-green-600 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Imagen --}}
            <div>
                @if($product->primaryImage)
                    <img
                        src="{{ asset('storage/' . $product->primaryImage->path) }}"
                        class="w-full rounded border object-cover"
                        alt=""
                    >
                @else
                    <div class="w-full h-80 rounded border bg-gray-50 flex items-center justify-center text-gray-400">
                        Sin imagen
                    </div>
                @endif

                @if($product->images->isNotEmpty())
                    <div class="grid grid-cols-4 gap-2 mt-3">
                        @foreach($product->images as $img)
                            <img
                                src="{{ asset('storage/' . $img->path) }}"
                                class="w-full h-20 object-cover rounded border"
                                alt=""
                            >
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div>
                <div class="text-2xl font-semibold mb-2">
                    ${{ number_format($product->price) }}
                </div>

                @if($product->description)
                    <p class="text-gray-700 mb-4">
                        {{ $product->description }}
                    </p>
                @endif

                <div class="text-sm text-gray-500 mb-4">
                    Stock: {{ $product->stock }}
                </div>

                {{-- Agregar al carrito (POST real) --}}
                <form method="POST" action="{{ route('storefront.cart.add', [$store, $product]) }}">
                    @csrf
                    <input type="hidden" name="qty" value="1">

                    <button type="submit" class="px-4 py-2 bg-black text-white rounded-md">
                        Agregar al carrito
                    </button>
                </form>

                <div class="mt-6 text-xs text-gray-400">
                    SKU: {{ $product->sku ?? '—' }}
                </div>
            </div>

        </div>
    </div>
</x-guest-layout>