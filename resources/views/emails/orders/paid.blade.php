<x-mail::message>
# ¡Tu pedido está confirmado!

Hola **{{ $order->customer_name }}**, tu pago fue recibido con éxito en **{{ $store->name }}**.

---

## Detalle de tu orden #{{ $order->id }}

<x-mail::table>
| Producto | Cant. | Precio |
|---|:---:|---:|
@foreach ($order->items as $item)
| {{ $item->name }} | {{ $item->qty }} | ${{ number_format($item->unit_price) }} |
@endforeach
</x-mail::table>

**Total pagado: ${{ number_format($order->total) }} {{ $order->currency }}**

---

<x-mail::button :url="$orderUrl">
Ver mi pedido
</x-mail::button>

Si tienes alguna duda puedes responder este correo.

Gracias,<br>
**{{ $store->name }}**
</x-mail::message>
