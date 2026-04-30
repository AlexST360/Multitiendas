<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-gray-700">← Órdenes</a>
            <span class="text-gray-300">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Orden #{{ $order->id }}</h2>
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
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">

        {{-- Cliente y envío --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-white rounded-lg border p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Cliente</h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex gap-2"><dt class="text-gray-400 w-20">Nombre</dt><dd>{{ $order->customer_name }}</dd></div>
                    <div class="flex gap-2"><dt class="text-gray-400 w-20">Email</dt><dd>{{ $order->customer_email }}</dd></div>
                    @if($order->customer_phone)
                    <div class="flex gap-2"><dt class="text-gray-400 w-20">Teléfono</dt><dd>{{ $order->customer_phone }}</dd></div>
                    @endif
                    <div class="flex gap-2"><dt class="text-gray-400 w-20">Fecha</dt><dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if($order->paid_at)
                    <div class="flex gap-2"><dt class="text-gray-400 w-20">Pagado</dt><dd>{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-lg border p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Dirección de envío</h3>
                @if($order->shipping_address)
                    <div class="text-sm text-gray-700 space-y-0.5">
                        <div>{{ $order->shipping_address }}</div>
                        <div>{{ $order->shipping_city }}@if($order->shipping_region), {{ $order->shipping_region }}@endif</div>
                        @if($order->shipping_notes)
                            <div class="text-gray-400 text-xs mt-1">{{ $order->shipping_notes }}</div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-400">Sin dirección registrada.</p>
                @endif
            </div>

        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-lg border overflow-hidden">
            <div class="px-5 py-3 border-b bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Productos</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="text-gray-500 bg-gray-50 border-b">
                    <tr>
                        <th class="px-5 py-2 text-left">Producto</th>
                        <th class="px-5 py-2 text-right">Precio unit.</th>
                        <th class="px-5 py-2 text-right">Qty</th>
                        <th class="px-5 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="px-5 py-2">{{ $item->product_name }}</td>
                            <td class="px-5 py-2 text-right">${{ number_format($item->unit_price) }}</td>
                            <td class="px-5 py-2 text-right">{{ $item->quantity }}</td>
                            <td class="px-5 py-2 text-right font-medium">${{ number_format($item->unit_price * $item->quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t bg-gray-50 text-sm">
                    <tr>
                        <td colspan="3" class="px-5 py-2 text-right text-gray-500">Subtotal</td>
                        <td class="px-5 py-2 text-right">${{ number_format($order->subtotal) }}</td>
                    </tr>
                    @if($order->discount > 0)
                    <tr>
                        <td colspan="3" class="px-5 py-2 text-right text-gray-500">
                            Descuento
                            @if($order->coupon_code)
                                <span class="font-mono text-xs">({{ $order->coupon_code }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-2 text-right text-green-700">-${{ number_format($order->discount) }}</td>
                    </tr>
                    @endif
                    <tr class="font-semibold">
                        <td colspan="3" class="px-5 py-2 text-right">Total</td>
                        <td class="px-5 py-2 text-right">${{ number_format($order->total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Envío --}}
        @if($order->shipment)
        <div class="bg-white rounded-lg border p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Envío</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex gap-2"><dt class="text-gray-400 w-28">Estado</dt><dd>{{ ucfirst($order->shipment->status->value) }}</dd></div>
                @if($order->shipment->carrier)
                <div class="flex gap-2"><dt class="text-gray-400 w-28">Transportista</dt><dd>{{ $order->shipment->carrier }}</dd></div>
                @endif
                @if($order->shipment->tracking_number)
                <div class="flex gap-2"><dt class="text-gray-400 w-28">Tracking</dt><dd class="font-mono">{{ $order->shipment->tracking_number }}</dd></div>
                @endif
                @if($order->shipment->shipped_at)
                <div class="flex gap-2"><dt class="text-gray-400 w-28">Despachado</dt><dd>{{ $order->shipment->shipped_at->format('d/m/Y H:i') }}</dd></div>
                @endif
                @if($order->shipment->delivered_at)
                <div class="flex gap-2"><dt class="text-gray-400 w-28">Entregado</dt><dd>{{ $order->shipment->delivered_at->format('d/m/Y H:i') }}</dd></div>
                @endif
            </dl>
            <div class="mt-3">
                <a href="{{ route('admin.shipments.edit', $order->shipment) }}"
                   class="text-sm underline text-gray-600">Editar envío</a>
            </div>
        </div>
        @elseif($order->status === \App\Domain\Orders\Enums\OrderStatus::Paid)
        <div class="bg-white rounded-lg border p-5 flex items-center justify-between">
            <p class="text-sm text-gray-500">Sin envío registrado.</p>
            <a href="{{ route('admin.shipments.create', $order) }}"
               class="px-4 py-2 bg-black text-white text-sm rounded hover:bg-gray-800">
                Crear envío
            </a>
        </div>
        @endif

        {{-- Pagos --}}
        @if($order->payments->isNotEmpty())
        <div class="bg-white rounded-lg border overflow-hidden">
            <div class="px-5 py-3 border-b bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Pagos</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="text-gray-500 bg-gray-50 border-b">
                    <tr>
                        <th class="px-5 py-2 text-left">Gateway</th>
                        <th class="px-5 py-2 text-left">Referencia</th>
                        <th class="px-5 py-2 text-right">Monto</th>
                        <th class="px-5 py-2 text-left">Estado</th>
                        <th class="px-5 py-2 text-left">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($order->payments as $payment)
                        <tr>
                            <td class="px-5 py-2 capitalize">{{ $payment->gateway }}</td>
                            <td class="px-5 py-2 font-mono text-xs text-gray-500">{{ $payment->gateway_reference ?? '—' }}</td>
                            <td class="px-5 py-2 text-right">${{ number_format($payment->amount) }}</td>
                            <td class="px-5 py-2">{{ $payment->status->value }}</td>
                            <td class="px-5 py-2 text-xs text-gray-400">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Acciones --}}
        <div class="flex gap-3">
            @if($order->status === \App\Domain\Orders\Enums\OrderStatus::Paid)
                <a href="{{ route('storefront.order.boleta', [$order->store->slug ?? '', $order->public_token]) }}"
                   target="_blank"
                   class="px-4 py-2 border text-sm rounded text-gray-700 hover:bg-gray-50">
                    Descargar boleta
                </a>
            @endif
            <a href="{{ route('storefront.order.thankyou', [$order->store->slug ?? '', $order->public_token]) }}"
               target="_blank"
               class="px-4 py-2 border text-sm rounded text-gray-700 hover:bg-gray-50">
                Ver página cliente
            </a>
        </div>

    </div>
</x-app-layout>
