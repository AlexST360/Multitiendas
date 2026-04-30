<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cupones</h2>
            <a href="{{ route('admin.coupons.create') }}"
               class="px-4 py-2 bg-black text-white text-sm rounded hover:bg-gray-800">
                + Nuevo cupón
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if($coupons->isEmpty())
            <div class="bg-white rounded-lg border px-6 py-10 text-center text-gray-400">
                No hay cupones creados.
            </div>
        @else
            <div class="bg-white rounded-lg border overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Código</th>
                            <th class="px-4 py-3 text-left">Tipo</th>
                            <th class="px-4 py-3 text-left">Descuento</th>
                            <th class="px-4 py-3 text-left">Usos</th>
                            <th class="px-4 py-3 text-left">Vence</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($coupons as $coupon)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-semibold">{{ $coupon->code }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $coupon->type->label() }}</td>
                                <td class="px-4 py-3 font-semibold">
                                    @if($coupon->type->value === 'percent')
                                        {{ $coupon->value }}%
                                    @else
                                        ${{ number_format($coupon->value) }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $coupon->uses_count }}
                                    @if($coupon->max_uses) / {{ $coupon->max_uses }} @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    {{ $coupon->expires_at?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($coupon->active)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 flex gap-3">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}"
                                       class="text-sm underline">Editar</a>
                                    <form method="POST" action="{{ route('admin.coupons.toggleActive', $coupon) }}">
                                        @csrf
                                        <button type="submit" class="text-sm underline text-gray-500">
                                            {{ $coupon->active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $coupons->links() }}</div>
        @endif

    </div>
</x-app-layout>
