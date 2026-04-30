<div class="space-y-4">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
        <input name="code"
               value="{{ old('code', $coupon->code ?? '') }}"
               class="w-full border-gray-300 rounded uppercase"
               placeholder="Ej: VERANO20"
               style="text-transform:uppercase"
               required>
        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de descuento *</label>
        <select name="type" class="w-full border-gray-300 rounded" required>
            @foreach(\App\Domain\Coupons\Enums\CouponType::cases() as $type)
                <option value="{{ $type->value }}"
                    {{ old('type', $coupon->type->value ?? '') === $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Valor * <span class="text-gray-400 font-normal">(% o CLP según tipo)</span></label>
        <input name="value" type="number" min="1"
               value="{{ old('value', $coupon->value ?? '') }}"
               class="w-full border-gray-300 rounded"
               required>
        @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pedido mínimo (CLP)</label>
        <input name="min_order_amount" type="number" min="0"
               value="{{ old('min_order_amount', $coupon->min_order_amount ?? '') }}"
               class="w-full border-gray-300 rounded"
               placeholder="Sin mínimo">
        @error('min_order_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Límite de usos</label>
        <input name="max_uses" type="number" min="1"
               value="{{ old('max_uses', $coupon->max_uses ?? '') }}"
               class="w-full border-gray-300 rounded"
               placeholder="Sin límite">
        @error('max_uses') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
        <input name="expires_at" type="date"
               value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at->format('Y-m-d') : '') }}"
               class="w-full border-gray-300 rounded">
        @error('expires_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="active" id="active" value="1"
               {{ old('active', $coupon->active ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300">
        <label for="active" class="text-sm text-gray-700">Activo</label>
    </div>

</div>
