<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BoletaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Descarga pública de boleta por token (acceso anónimo)
    |--------------------------------------------------------------------------
    | Solo disponible si la orden está pagada.
    */
    public function download(Store $store, string $token): Response
    {
        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('public_token', $token)
            ->with('items')
            ->firstOrFail();

        abort_unless($order->status === OrderStatus::Paid, 403, 'La boleta solo está disponible para órdenes pagadas.');

        $pdf = Pdf::loadView('pdf.boleta', compact('order', 'store'))
            ->setPaper('a4', 'portrait');

        $filename = 'boleta-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }
}
