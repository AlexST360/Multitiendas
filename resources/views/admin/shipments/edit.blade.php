<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Envío — Orden #{{ $shipment->order_id }}</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">

        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- Destinatario --}}
        <div class="bg-white rounded-lg border p-6 mb-6">
            <h3 class="font-semibold mb-3">Destinatario</h3>
            <div class="text-sm text-gray-700 space-y-1">
                <div><span class="text-gray-500">Nombre:</span> {{ $shipment->order->customer_name }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $shipment->order->customer_email }}</div>
                <div><span class="text-gray-500">Teléfono:</span> {{ $shipment->order->customer_phone ?? '—' }}</div>
                <div class="pt-2 border-t mt-2">
                    <span class="text-gray-500">Dirección:</span>
                    {{ $shipment->order->shipping_address }}, {{ $shipment->order->shipping_city }}, {{ $shipment->order->shipping_region }}
                </div>
                @if($shipment->order->shipping_notes)
                    <div><span class="text-gray-500">Instrucciones:</span> {{ $shipment->order->shipping_notes }}</div>
                @endif
            </div>
        </div>

        {{-- Formulario --}}
        <div class="bg-white rounded-lg border p-6">
            <h3 class="font-semibold mb-4">Datos del envío</h3>

            <form method="POST" action="{{ route('admin.shipments.update', $shipment) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                        <select name="status" class="w-full border-gray-300 rounded" required>
                            @foreach(\App\Domain\Shipments\Enums\ShipmentStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ $shipment->status === $s ? 'selected' : '' }}>
                                    {{ $s->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Courier *</label>
                        <select name="carrier" class="w-full border-gray-300 rounded" required>
                            @foreach(['Chilexpress','Starken','BlueExpress','Correos de Chile','Otro'] as $c)
                                <option value="{{ $c }}" {{ $shipment->carrier === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de seguimiento</label>
                        <input name="tracking_number" value="{{ old('tracking_number', $shipment->tracking_number) }}"
                               class="w-full border-gray-300 rounded">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL de seguimiento</label>
                        <input name="tracking_url" type="url" value="{{ old('tracking_url', $shipment->tracking_url) }}"
                               class="w-full border-gray-300 rounded">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas internas</label>
                        <textarea name="notes" rows="2"
                                  class="w-full border-gray-300 rounded">{{ old('notes', $shipment->notes) }}</textarea>
                    </div>
                </div>

                {{-- Timestamps --}}
                <div class="mt-5 pt-4 border-t text-xs text-gray-500 space-y-1">
                    <div>Despachado: {{ $shipment->shipped_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    <div>Entregado: {{ $shipment->delivered_at?->format('d/m/Y H:i') ?? '—' }}</div>
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
                        Guardar cambios
                    </button>
                    <a href="{{ route('admin.shipments.index') }}"
                       class="px-5 py-2 border rounded hover:bg-gray-50">
                        Volver
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
