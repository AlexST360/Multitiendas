<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Órdenes</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4">

        {{-- Filtros --}}
        <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nombre, email o # orden"
                       class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-black focus:border-black w-56">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-black focus:border-black">
                    <option value="">Todos</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $s->value)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-black text-white text-sm rounded hover:bg-gray-800">
                Filtrar
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.orders.index') }}"
                   class="px-4 py-2 border text-sm rounded text-gray-600 hover:bg-gray-50">
                    Limpiar
                </a>
            @endif
        </form>

        @if($orders->isEmpty())
            <div class="bg-white rounded-lg border px-6 py-10 text-center text-gray-400">
                No hay órdenes.
            </div>
        @else
            <div class="bg-white rounded-lg border overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Cliente</th>
                            <th class="px-4 py-3 text-left">Total</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-left">Envío</th>
                            <th class="px-4 py-3 text-left">Fecha</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-500 text-xs">#{{ $order->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-400">{{ $order->customer_email }}</div>
                                </td>
                                <td class="px-4 py-3 font-semibold">${{ number_format($order->total) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($order->status) {
                                            \App\Domain\Orders\Enums\OrderStatus::Paid           => 'bg-green-100 text-green-800',
                                            \App\Domain\Orders\Enums\OrderStatus::PendingPayment  => 'bg-yellow-100 text-yellow-800',
                                            \App\Domain\Orders\Enums\OrderStatus::Cancelled       => 'bg-gray-100 text-gray-600',
                                            \App\Domain\Orders\Enums\OrderStatus::Failed          => 'bg-red-100 text-red-700',
                                            \App\Domain\Orders\Enums\OrderStatus::Refunded        => 'bg-purple-100 text-purple-700',
                                            default                                               => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status->value)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if($order->shipment)
                                        {{ ucfirst($order->shipment->status->value) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="text-sm underline">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif

    </div>
</x-app-layout>
