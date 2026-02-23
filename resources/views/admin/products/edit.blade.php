<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar producto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4">
                        <ul class="text-sm text-red-600 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input name="name" value="{{ old('name', $product->name) }}" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" class="mt-1 block w-full border-gray-300 rounded-md" rows="4">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio (CLP)</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" class="mt-1 block w-full border-gray-300 rounded-md" min="0" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="mt-1 block w-full border-gray-300 rounded-md" min="0" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input name="sku" value="{{ old('sku', $product->sku) }}" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="active" type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }}>
                        <label for="active" class="text-sm text-gray-700">Activo</label>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border rounded-md">
                            Volver
                        </a>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md">
                            Guardar cambios
                        </button>
                    </div>

                </form>

                {{-- ========= IMÁGENES ========= --}}
                <hr class="my-6">

                <h3 class="font-semibold text-lg mb-3">Imágenes</h3>

                @if ($errors->has('image'))
                    <div class="mb-2 text-sm text-red-600">
                        {{ $errors->first('image') }}
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('admin.products.images.store', $product) }}"
                      enctype="multipart/form-data"
                      class="flex items-center gap-3">
                    @csrf
                    <input type="file" name="image" accept="image/*" required>
                    <button type="submit" class="px-4 py-2 bg-black text-white rounded-md">
                        Subir
                    </button>
                </form>

                @if($product->images->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                        @foreach($product->images as $img)
                            <div class="border rounded-md p-2">
                                <img src="{{ asset('storage/' . $img->path) }}"
                                     class="w-full h-40 object-cover rounded"
                                     alt="">

                                <div class="flex justify-between items-center mt-2 text-sm">
                                    <span class="{{ $img->is_primary ? 'font-semibold' : '' }}">
                                        {{ $img->is_primary ? 'Principal' : '' }}
                                    </span>

                                    <form method="POST"
                                          action="{{ route('admin.products.images.destroy', [$product, $img]) }}"
                                          onsubmit="return confirm('¿Eliminar imagen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 underline">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>