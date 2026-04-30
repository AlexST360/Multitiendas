<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Orders\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;

        $query = Order::query()
            ->where('store_id', $storeId)
            ->with(['shipment'])
            ->latest();

        // Filtro por estado
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Búsqueda por nombre, email o id
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_email', 'like', '%' . $search . '%')
                  ->orWhere('id', (int) $search ?: 0);
            });
        }

        $orders   = $query->paginate(25)->withQueryString();
        $statuses = OrderStatus::cases();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->store_id === auth()->user()->store_id, 403);

        $order->load(['items', 'payments', 'shipment']);

        return view('admin.orders.show', compact('order'));
    }
}
