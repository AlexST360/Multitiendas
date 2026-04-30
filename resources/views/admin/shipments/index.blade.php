<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Envíos</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4">

        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- Órdenes pagadas sin envío --}}
        @if($pendingOrders->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-3">Órdenes sin envío asignado</h3>
                <div class="bg-white rounded-lg border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left"># Orden</th>
                                <th class="px-4 py-3 text-left">Cliente</th>
                                <th class="px-4 py-3 text-left">Dirección</th>
                                <th class="px-4 py-3 text-left">Total</th>
                                <th class="px-4 py-3 text-left">Pagada</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($pendingOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono">#{{ $order->id }}</td>
                                    <td class="px-4 py-3">
                                        <div>{{ $order->customer_name }}</div>
                                        <div class="text-gray-500">{{ $order->customer_email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $order->shipping_address }}, {{ $order->shipping_city }}<br>
                                        <span class="text-xs">{{ $order->shipping_region }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">${{ number_format($order->total) }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        {{ $order->paid_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.shipments.create', $order) }}"
                                           class="px-3 py-1.5 bg-black text-white text-xs rounded hover:bg-gray-800">
                                            Crear envío
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Envíos activos --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Envíos</h3>
                <div class="flex gap-2 text-sm">
                    <a href="{{ route('admin.shipments.index') }}"
                       class="px-3 py-1 rounded border {{ !$status ? 'bg-black text-white border-black' : 'border-gray-300' }}">
                        Todos
                    </a>
                    @foreach(['pending','preparing','shipped','delivered','returned'] as $s)
                        <a href="{{ route('admin.shipments.index', ['status' => $s]) }}"
                           class="px-3 py-1 rounded border {{ $status === $s ? 'bg-black text-white border-black' : 'border-gray-300' }}">
                            {{ ucfirst($s) }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if($shipments->isEmpty())
                <div class="bg-white rounded-lg border px-6 py-10 text-center text-gray-400">
                    No hay envíos{{ $status ? ' con estado "' . $status . '"' : '' }}.
                </div>
            @else
                <div class="bg-white rounded-lg border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left"># Orden</th>
                                <th class="px-4 py-3 text-left">Cliente</th>
                                <th class="px-4 py-3 text-left">Courier</th>
                                <th class="px-4 py-3 text-left">Tracking</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-left">Despacho</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($shipments as $shipment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono">#{{ $shipment->order_id }}</td>
                                    <td class="px-4 py-3">{{ $shipment->order->customer_name }}</td>
                                    <td class="px-4 py-3">{{ $shipment->carrier ?? '—' }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">
                                        @if($shipment->tracking_number)
                                            @if($shipment->tracking_url)
                                                <a href="{{ $shipment->tracking_url }}" target="_blank"
                                                   class="text-blue-600 underline">
                                                    {{ $shipment->tracking_number }}
                                                </a>
                                            @else
                                                {{ $shipment->tracking_number }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $colors = [
                                                'pending'   => 'bg-gray-100 text-gray-700',
                                                'preparing' => 'bg-yellow-100 text-yellow-800',
                                                'shipped'   => 'bg-blue-100 text-blue-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'returned'  => 'bg-red-100 text-red-800',
                                            ];
                                            $color = $colors[$shipment->status->value] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                            {{ $shipment->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        {{ $shipment->shipped_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.shipments.edit', $shipment) }}"
                                           class="text-sm underline">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $shipments->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
