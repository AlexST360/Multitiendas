<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #111;
        padding: 40px;
    }

    /* ── Cabecera ── */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #111;
        padding-bottom: 20px;
        margin-bottom: 28px;
    }
    .store-name {
        font-size: 22px;
        font-weight: bold;
        letter-spacing: -0.5px;
    }
    .doc-info {
        text-align: right;
    }
    .doc-title {
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .doc-number {
        font-size: 13px;
        color: #444;
        margin-top: 4px;
    }
    .doc-date {
        font-size: 11px;
        color: #666;
        margin-top: 2px;
    }

    /* ── Sección datos cliente / envío ── */
    .info-grid {
        display: flex;
        gap: 40px;
        margin-bottom: 28px;
    }
    .info-block {
        flex: 1;
    }
    .info-block h3 {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        margin-bottom: 6px;
    }
    .info-block p {
        line-height: 1.6;
        color: #222;
    }

    /* ── Tabla de items ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    thead tr {
        background: #111;
        color: #fff;
    }
    thead th {
        padding: 8px 10px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    thead th.right { text-align: right; }
    tbody tr {
        border-bottom: 1px solid #eee;
    }
    tbody tr:nth-child(even) {
        background: #f9f9f9;
    }
    tbody td {
        padding: 8px 10px;
        vertical-align: top;
    }
    tbody td.right {
        text-align: right;
        white-space: nowrap;
    }
    .sku {
        font-size: 10px;
        color: #888;
    }

    /* ── Totales ── */
    .totals {
        width: 260px;
        margin-left: auto;
        margin-bottom: 32px;
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid #eee;
        font-size: 12px;
    }
    .totals-row.total {
        font-size: 14px;
        font-weight: bold;
        border-bottom: 2px solid #111;
        padding-top: 8px;
    }

    /* ── Estado de pago ── */
    .payment-badge {
        display: inline-block;
        background: #d1fae5;
        color: #065f46;
        font-size: 11px;
        font-weight: bold;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Pie ── */
    .footer {
        margin-top: 40px;
        border-top: 1px solid #ddd;
        padding-top: 14px;
        font-size: 10px;
        color: #999;
        text-align: center;
    }
</style>
</head>
<body>

    {{-- Cabecera --}}
    <div class="header">
        <div>
            <div class="store-name">{{ $store->name }}</div>
        </div>
        <div class="doc-info">
            <div class="doc-title">Boleta de venta</div>
            <div class="doc-number"># {{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="doc-date">{{ $order->paid_at?->format('d/m/Y H:i') ?? $order->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- Datos cliente y envío --}}
    <div class="info-grid">
        <div class="info-block">
            <h3>Cliente</h3>
            <p>
                {{ $order->customer_name }}<br>
                {{ $order->customer_email }}<br>
                @if($order->customer_phone){{ $order->customer_phone }}@endif
            </p>
        </div>
        <div class="info-block">
            <h3>Dirección de envío</h3>
            <p>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_city }}, {{ $order->shipping_region }}
                @if($order->shipping_notes)<br><em>{{ $order->shipping_notes }}</em>@endif
            </p>
        </div>
        <div class="info-block" style="text-align:right">
            <h3>Estado de pago</h3>
            <span class="payment-badge">Pagado</span>
            @if($order->paid_at)
                <p style="margin-top:6px; font-size:11px; color:#666">
                    {{ $order->paid_at->format('d/m/Y H:i') }}
                </p>
            @endif
        </div>
    </div>

    {{-- Tabla de productos --}}
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="right">Precio unit.</th>
                <th class="right">Cant.</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->name }}
                    @if($item->sku)<div class="sku">SKU: {{ $item->sku }}</div>@endif
                </td>
                <td class="right">${{ number_format($item->unit_price) }}</td>
                <td class="right">{{ $item->qty }}</td>
                <td class="right">${{ number_format($item->line_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales --}}
    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>${{ number_format($order->subtotal) }}</span>
        </div>
        <div class="totals-row total">
            <span>Total {{ $order->currency }}</span>
            <span>${{ number_format($order->total) }}</span>
        </div>
    </div>

    {{-- Pie --}}
    <div class="footer">
        Documento generado por {{ $store->name }} &mdash; {{ config('app.url') }}<br>
        Este documento es una boleta de venta electrónica.
    </div>

</body>
</html>
