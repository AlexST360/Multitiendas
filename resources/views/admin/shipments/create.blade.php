<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear envío — Orden #{{ $order->id }}</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">

        {{-- Datos del destinatario --}}
        <div class="bg-white rounded-lg border p-6 mb-6">
            <h3 class="font-semibold mb-3">Destinatario</h3>
            <div class="text-sm text-gray-700 space-y-1">
                <div><span class="text-gray-500">Nombre:</span> {{ $order->customer_name }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</div>
                <div><span class="text-gray-500">Teléfono:</span> {{ $order->customer_phone ?? '—' }}</div>
                <div class="pt-2 border-t mt-2">
                    <span class="text-gray-500">Dirección:</span>
                    {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_region }}
                </div>
                @if($order->shipping_notes)
                    <div><span class="text-gray-500">Instrucciones:</span> {{ $order->shipping_notes }}</div>
                @endif
            </div>
        </div>

        {{-- Formulario --}}
        <div class="bg-white rounded-lg border p-6">
            <h3 class="font-semibold mb-4">Datos del envío</h3>

            <form method="POST" action="{{ route('admin.shipments.store', $order) }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Courier *</label>
                        <select name="carrier" class="w-full border-gray-300 rounded" required>
                            <option value="">Seleccionar</option>
                            @foreach(['Chilexpress','Starken','BlueExpress','Correos de Chile','Otro'] as $c)
                                <option value="{{ $c }}" {{ old('carrier') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de seguimiento</label>
                        <input name="tracking_number" value="{{ old('tracking_number') }}"
                               class="w-full border-gray-300 rounded" placeholder="Ej: CX123456789">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL de seguimiento</label>
                        <input name="tracking_url" type="url" value="{{ old('tracking_url') }}"
                               class="w-full border-gray-300 rounded" placeholder="https://...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas internas</label>
                        <textarea name="notes" rows="2"
                                  class="w-full border-gray-300 rounded">{{ old('notes') }}</textarea>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mt-4 text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-5 py-2 bg-black text-white rounded hover:bg-gray-800">
                        Crear envío
                    </button>
                    <a href="{{ route('admin.shipments.index') }}"
                       class="px-5 py-2 border rounded hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
