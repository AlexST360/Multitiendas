<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Productos
            </h2>

            <a href="{{ route('admin.products.create') }}"
               class="px-4 py-2 bg-black text-white rounded-md">
                Crear producto
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if(session('status'))
                    <div class="mb-4 text-green-600 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if($products->isEmpty())
                    <p>No hay productos aún.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Imagen</th>
                                    <th class="px-4 py-2 text-left">Nombre</th>
                                    <th class="px-4 py-2 text-left">Precio</th>
                                    <th class="px-4 py-2 text-left">Stock</th>
                                    <th class="px-4 py-2 text-left">Activo</th>
                                    <th class="px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr class="border-t">
                                        <td class="px-4 py-2">
                                            @if($product->primaryImage)
                                                <img
                                                    src="{{ asset('storage/' . $product->primaryImage->path) }}"
                                                    class="w-12 h-12 object-cover rounded border"
                                                    alt=""
                                                >
                                            @else
                                                <div class="w-12 h-12 rounded border bg-gray-50 flex items-center justify-center text-xs text-gray-400">
                                                    —
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-2">
                                            <a class="underline" href="{{ route('admin.products.edit', $product) }}">
                                                {{ $product->name }}
                                            </a>
                                        </td>

                                        <td class="px-4 py-2">
                                            ${{ number_format($product->price) }}
                                        </td>

                                        <td class="px-4 py-2">
                                            {{ $product->stock }}
                                        </td>

                                        <td class="px-4 py-2">
                                            {{ $product->active ? 'Sí' : 'No' }}
                                        </td>

                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-3">
                                                <a class="underline" href="{{ route('admin.products.edit', $product) }}">
                                                    Editar
                                                </a>

                                                <form method="POST" action="{{ route('admin.products.toggleActive', $product) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="underline {{ $product->active ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ $product->active ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>