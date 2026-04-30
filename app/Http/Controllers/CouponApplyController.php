<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponApplyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Aplicar cupón en checkout (guarda en sesión)
    |--------------------------------------------------------------------------
    */
    public function apply(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $code   = strtoupper(trim($data['coupon_code']));
        $coupon = Coupon::query()
            ->where('store_id', $store->id)
            ->where('code', $code)
            ->where('active', true)
            ->first();

        if (! $coupon) {
            return back()->withErrors(['coupon_code' => 'Cupón no válido.']);
        }

        // Calcular subtotal actual del carrito para validar monto mínimo
        $cart     = session()->get("cart_{$store->id}", []);
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['qty']);

        if (! $coupon->isValid($subtotal)) {
            $message = match(true) {
                $coupon->expires_at?->isPast()                         => 'Este cupón ha vencido.',
                $coupon->max_uses !== null && $coupon->uses_count >= $coupon->max_uses => 'Este cupón ya alcanzó el límite de usos.',
                $coupon->min_order_amount !== null && $subtotal < $coupon->min_order_amount
                    => 'El pedido mínimo para este cupón es $' . number_format($coupon->min_order_amount) . '.',
                default => 'Cupón no válido.',
            };

            return back()->withErrors(['coupon_code' => $message]);
        }

        session()->put("coupon_{$store->id}", $coupon->code);

        return back()->with('status', "Cupón {$coupon->code} aplicado.");
    }

    /*
    |--------------------------------------------------------------------------
    | Quitar cupón
    |--------------------------------------------------------------------------
    */
    public function remove(Store $store): RedirectResponse
    {
        session()->forget("coupon_{$store->id}");

        return back()->with('status', 'Cupón eliminado.');
    }
}
