<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear producto
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

                <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input name="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" class="mt-1 block w-full border-gray-300 rounded-md" rows="4">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio (CLP)</label>
                            <input type="number" name="price" value="{{ old('price', 0) }}" class="mt-1 block w-full border-gray-300 rounded-md" min="0" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" class="mt-1 block w-full border-gray-300 rounded-md" min="0" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input name="sku" value="{{ old('sku') }}" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="active" type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                        <label for="active" class="text-sm text-gray-700">Activo</label>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border rounded-md">
                            Volver
                        </a>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md">
                            Guardar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>