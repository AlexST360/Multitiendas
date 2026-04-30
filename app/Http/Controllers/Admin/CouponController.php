<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Coupons\Enums\CouponType;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()
            ->where('store_id', auth()->user()->store_id)
            ->latest()
            ->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Coupon::create([...$data, 'store_id' => auth()->user()->store_id]);

        return redirect()->route('admin.coupons.index')->with('status', 'Cupón creado.');
    }

    public function edit(Coupon $coupon): View
    {
        abort_unless($coupon->store_id === auth()->user()->store_id, 403);

        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        abort_unless($coupon->store_id === auth()->user()->store_id, 403);

        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('admin.coupons.index')->with('status', 'Cupón actualizado.');
    }

    public function toggleActive(Coupon $coupon): RedirectResponse
    {
        abort_unless($coupon->store_id === auth()->user()->store_id, 403);

        $coupon->update(['active' => ! $coupon->active]);

        return back()->with('status', $coupon->active ? 'Cupón activado.' : 'Cupón desactivado.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $storeId = auth()->user()->store_id;

        $codeRule = $coupon
            ? 'required|string|max:50|unique:coupons,code,' . $coupon->id . ',id,store_id,' . $storeId
            : 'required|string|max:50|unique:coupons,code,NULL,id,store_id,' . $storeId;

        $data = $request->validate([
            'code'             => [$codeRule],
            'type'             => ['required', 'in:percent,fixed'],
            'value'            => ['required', 'integer', 'min:1'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'expires_at'       => ['nullable', 'date', 'after:today'],
            'active'           => ['boolean'],
        ]);

        $data['code']   = strtoupper(trim($data['code']));
        $data['active'] = $request->boolean('active', true);

        // Validación semántica: porcentaje no puede superar 100
        if ($data['type'] === 'percent' && $data['value'] > 100) {
            $request->validate(['value' => ['max:100']]);
        }

        return $data;
    }
}
