<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Domain\Orders\Enums\OrderStatus; // ✅ NUEVO (solo agregado)
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Catálogo público de una tienda
     * /s/{storeSlug}
     */
    public function index(Store $store): View
    {
        $products = $store->products()
            ->where('active', true)
            ->with(['primaryImage'])
            ->latest()
            ->paginate(12);

        return view('storefront.store', compact('store', 'products'));
    }

    /**
     * Detalle de producto
     * /s/{storeSlug}/p/{productSlug}
     */
    public function show(Store $store, Product $product): View
    {
        abort_unless($product->store_id === $store->id, 404);
        abort_unless($product->active, 404);

        $product->load(['primaryImage', 'images']);

        return view('storefront.product', compact('store', 'product'));
    }

    /**
     * POST: agregar al carrito (por tienda) y redirigir a checkout
     * /s/{storeSlug}/cart/add/{productSlug}
     */
    public function addToCart(Request $request, Store $store, Product $product): RedirectResponse
    {
        abort_unless($product->store_id === $store->id, 404);
        abort_unless($product->active, 404);

        $data = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $qty = (int) ($data['qty'] ?? 1);

        if ($product->stock < $qty) {
            return redirect()
                ->route('storefront.product.show', [$store, $product])
                ->with('status', 'Stock insuficiente');
        }

        $cartKey = $this->cartKey($store->id);
        $cart = session()->get($cartKey, []);

        $id = (string) $product->id;

        if (!isset($cart[$id])) {
            $cart[$id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (int) $product->price,
                'qty' => 0,
                'image_path' => optional($product->primaryImage)->path,
            ];
        }

        $cart[$id]['qty'] += $qty;

        if ($cart[$id]['qty'] > $product->stock) {
            $cart[$id]['qty'] = $product->stock;
        }

        session()->put($cartKey, $cart);

        return redirect()
            ->route('storefront.checkout', $store)
            ->with('status', 'Agregado al carrito');
    }

    /**
     * GET: checkout simple (one-step)
     * /s/{storeSlug}/checkout
     */
    public function checkout(Store $store): View
    {
        $cartKey = $this->cartKey($store->id);
        $cart = session()->get($cartKey, []);

        $items = [];
        $subtotal = 0;

        if (!empty($cart)) {
            $productIds = collect($cart)->pluck('product_id')->all();

            $products = Product::query()
                ->where('store_id', $store->id)
                ->whereIn('id', $productIds)
                ->with('primaryImage')
                ->get()
                ->keyBy('id');

            foreach ($cart as $row) {
                $p = $products->get($row['product_id']);
                if (!$p || !$p->active) continue;

                $qty = (int) $row['qty'];
                if ($qty < 1) continue;

                if ($qty > $p->stock) $qty = $p->stock;
                if ($qty < 1) continue;

                $price = (int) $p->price;
                $lineTotal = $price * $qty;

                $items[] = [
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'price' => $price,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                    'image_path' => optional($p->primaryImage)->path,
                ];

                $subtotal += $lineTotal;
            }
        }

        // Cupón en sesión
        $coupon   = null;
        $discount = 0;

        $couponCode = session("coupon_{$store->id}");

        if ($couponCode) {
            $coupon = \App\Models\Coupon::where('store_id', $store->id)
                ->where('code', $couponCode)
                ->where('active', true)
                ->first();

            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                session()->forget("coupon_{$store->id}");
                $coupon = null;
            }
        }

        $total = max(0, $subtotal - $discount);

        return view('storefront.checkout', compact('store', 'items', 'subtotal', 'coupon', 'discount', 'total'));
    }

    /**
     * POST: actualizar cantidades (checkout)
     * /s/{storeSlug}/cart/update
     */
    public function updateCart(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $cartKey = $this->cartKey($store->id);
        $cart = session()->get($cartKey, []);

        foreach ($data['qty'] as $productId => $qty) {
            $productId = (string) $productId;
            if (!isset($cart[$productId])) continue;

            $qty = (int) $qty;

            if ($qty <= 0) {
                unset($cart[$productId]);
                continue;
            }

            $cart[$productId]['qty'] = $qty;
        }

        session()->put($cartKey, $cart);

        return redirect()
            ->route('storefront.checkout', $store)
            ->with('status', 'Carrito actualizado');
    }

    /**
     * POST: eliminar item
     * /s/{storeSlug}/cart/remove/{productId}
     */
    public function removeFromCart(Store $store, int $productId): RedirectResponse
    {
        $cartKey = $this->cartKey($store->id);
        $cart = session()->get($cartKey, []);

        unset($cart[(string) $productId]);

        session()->put($cartKey, $cart);

        return redirect()
            ->route('storefront.checkout', $store)
            ->with('status', 'Item eliminado');
    }

    /*
    |--------------------------------------------------------------------------
    | ÓRDENES (MVP)
    |--------------------------------------------------------------------------
    | placeOrder: crea Order + OrderItems (snapshots) con transacción
    | thankYou: página pública por token
    */

    public function placeOrder(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_email'   => ['required', 'email', 'max:255'],
            'customer_phone'   => ['nullable', 'string', 'max:50'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_city'    => ['required', 'string', 'max:100'],
            'shipping_region'  => ['required', 'string', 'max:100'],
            'shipping_notes'   => ['nullable', 'string', 'max:500'],
        ]);

        // Resolver cupón desde sesión
        $couponCode    = session("coupon_{$store->id}");
        $appliedCoupon = $couponCode
            ? \App\Models\Coupon::where('store_id', $store->id)->where('code', $couponCode)->where('active', true)->first()
            : null;

        $cartKey = $this->cartKey($store->id);
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return redirect()
                ->route('storefront.checkout', $store)
                ->with('status', 'Tu carrito está vacío');
        }

        $order = DB::transaction(function () use ($store, $cart, $data) {

            $productIds = collect($cart)->pluck('product_id')->all();

            $products = Product::query()
                ->where('store_id', $store->id)
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $items = [];
            $subtotal = 0;

            foreach ($cart as $row) {
                $p = $products->get($row['product_id']);
                if (!$p || !$p->active) continue;

                $qty = (int) $row['qty'];
                if ($qty < 1) continue;

                if ($qty > $p->stock) $qty = $p->stock;
                if ($qty < 1) continue;

                $unitPrice = (int) $p->price;
                $lineTotal = $unitPrice * $qty;

                $items[] = [
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'unit_price' => $unitPrice,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                ];

                $subtotal += $lineTotal;
            }

            if (empty($items)) {
                return null;
            }

            // Calcular descuento dentro de la transacción
            $discount    = 0;
            $couponToUse = null;

            if ($appliedCoupon && $appliedCoupon->isValid($subtotal)) {
                $discount    = $appliedCoupon->calculateDiscount($subtotal);
                $couponToUse = $appliedCoupon;
            }

            $total = max(0, $subtotal - $discount);

            $order = Order::create([
                'store_id'         => $store->id,
                'public_token'     => (string) Str::uuid(),
                'status'           => OrderStatus::PendingPayment,
                'customer_name'    => $data['customer_name'],
                'customer_email'   => $data['customer_email'],
                'customer_phone'   => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'shipping_city'    => $data['shipping_city'],
                'shipping_region'  => $data['shipping_region'],
                'shipping_notes'   => $data['shipping_notes'] ?? null,
                'coupon_code'      => $couponToUse?->code,
                'discount'         => $discount,
                'currency'         => 'CLP',
                'subtotal'         => $subtotal,
                'total'            => $total,
            ]);

            if ($couponToUse) {
                $couponToUse->increment('uses_count');
            }

            foreach ($items as $it) {
                OrderItem::create([
                    'store_id' => $store->id,
                    'order_id' => $order->id,
                    'product_id' => $it['product_id'],
                    'name' => $it['name'],
                    'sku' => $it['sku'],
                    'unit_price' => $it['unit_price'],
                    'qty' => $it['qty'],
                    'line_total' => $it['line_total'],
                ]);
            }

            return $order;
        });

        if (!$order) {
            return redirect()
                ->route('storefront.checkout', $store)
                ->with('status', 'No se pudo crear la orden (carrito inválido o sin productos activos)');
        }

        session()->forget($cartKey);
        session()->forget("coupon_{$store->id}");

        return redirect()
            ->route('storefront.order.thankyou', [$store, $order->public_token]);
    }

    public function thankYou(Store $store, string $token): View
    {
        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $token)
            ->with(['items', 'shipment'])
            ->firstOrFail();

        return view('storefront.thankyou', compact('store', 'order'));
    }

    private function cartKey(int $storeId): string
    {
        return "cart_{$storeId}";
    }
}