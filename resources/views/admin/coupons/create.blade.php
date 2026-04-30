<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo cupón</h2>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white rounded-lg border p-6">
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf
                @include('admin.coupons._form')
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-5 py-2 bg-black text-white rounded hover:bg-gray-800">
                        Crear cupón
                    </button>
                    <a href="{{ route('admin.coupons.index') }}" class="px-5 py-2 border rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
