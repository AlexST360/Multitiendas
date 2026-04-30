<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Shipments\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Lista de envíos de la tienda
    |--------------------------------------------------------------------------
    | Muestra todas las órdenes pagadas, con o sin envío asignado.
    */
    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;

        $status = $request->query('status');

        $shipments = Shipment::query()
            ->where('store_id', $storeId)
            ->with('order')
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        // Órdenes pagadas sin envío asignado
        $pendingOrders = Order::query()
            ->where('store_id', $storeId)
            ->where('status', 'paid')
            ->whereDoesntHave('shipment')
            ->latest()
            ->get();

        return view('admin.shipments.index', compact('shipments', 'pendingOrders', 'status'));
    }

    /*
    |--------------------------------------------------------------------------
    | Crear envío para una orden
    |--------------------------------------------------------------------------
    */
    public function create(Order $order): View
    {
        abort_unless($order->store_id === auth()->user()->store_id, 403);
        abort_if($order->shipment()->exists(), 409, 'Esta orden ya tiene un envío asignado.');

        return view('admin.shipments.create', compact('order'));
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->store_id === auth()->user()->store_id, 403);
        abort_if($order->shipment()->exists(), 409);

        $data = $request->validate([
            'carrier'         => ['required', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'tracking_url'    => ['nullable', 'url', 'max:500'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $shipment = Shipment::create([
            'store_id'        => $order->store_id,
            'order_id'        => $order->id,
            'status'          => ShipmentStatus::Preparing,
            'carrier'         => $data['carrier'],
            'tracking_number' => $data['tracking_number'] ?? null,
            'tracking_url'    => $data['tracking_url'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.shipments.edit', $shipment)
            ->with('status', 'Envío creado.');
    }

    /*
    |--------------------------------------------------------------------------
    | Editar / actualizar estado y tracking
    |--------------------------------------------------------------------------
    */
    public function edit(Shipment $shipment): View
    {
        abort_unless($shipment->store_id === auth()->user()->store_id, 403);

        $shipment->load('order');

        return view('admin.shipments.edit', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        abort_unless($shipment->store_id === auth()->user()->store_id, 403);

        $data = $request->validate([
            'status'          => ['required', 'string'],
            'carrier'         => ['required', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'tracking_url'    => ['nullable', 'url', 'max:500'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $newStatus = ShipmentStatus::from($data['status']);

        $update = [
            'status'          => $newStatus,
            'carrier'         => $data['carrier'],
            'tracking_number' => $data['tracking_number'] ?? null,
            'tracking_url'    => $data['tracking_url'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ];

        // Timestamps automáticos según transición de estado
        if ($newStatus === ShipmentStatus::Shipped && ! $shipment->shipped_at) {
            $update['shipped_at'] = now();
        }
        if ($newStatus === ShipmentStatus::Delivered && ! $shipment->delivered_at) {
            $update['delivered_at'] = now();
        }

        $shipment->update($update);

        return redirect()
            ->route('admin.shipments.edit', $shipment)
            ->with('status', 'Envío actualizado.');
    }
}
