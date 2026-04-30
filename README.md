# Multitiendas

Plataforma SaaS para crear y gestionar tiendas online. Cada negocio tiene su propia tienda con catálogo de productos, carrito de compras, pagos integrados, envíos y cupones de descuento — sin que los clientes necesiten crear una cuenta.

Desarrollado con Laravel 12 para el mercado chileno, con soporte para las pasarelas de pago locales.

---

## ¿Qué puede hacer?

**Para el dueño de la tienda**
- Crear y administrar productos con imágenes, precio, stock y SKU
- Ver todas las órdenes con estado, datos del cliente y detalle de productos
- Gestionar los envíos: crear, registrar tracking y marcar como despachado/entregado
- Crear cupones de descuento por porcentaje o monto fijo, con límite de usos y fecha de vencimiento
- Configurar las credenciales de Webpay y MercadoPago por tienda desde el panel admin
- Descargar la boleta PDF de cada orden pagada

**Para el comprador**
- Navegar el catálogo de la tienda
- Agregar productos al carrito y ajustar cantidades
- Aplicar cupones de descuento en el checkout
- Hacer el checkout ingresando solo nombre, email, teléfono y dirección de envío
- Pagar con Webpay Plus o MercadoPago
- Recibir email de confirmación automático al completar el pago
- Descargar la boleta PDF desde la página de confirmación

---

## Pasarelas de pago

| Pasarela | Estado |
|---|---|
| Webpay Plus (Transbank) | Funcional en sandbox |
| MercadoPago Checkout Pro | Funcional en sandbox |

Cada tienda puede tener sus propias credenciales configuradas en el panel admin. Si no se configuran, se usan las del archivo `.env` como fallback.

---

## Estado del proyecto

| Módulo | Estado |
|---|---|
| Multi-tienda y catálogo | ✅ Completo |
| Carrito y checkout | ✅ Completo |
| Órdenes | ✅ Completo |
| Pagos — Webpay Plus + MercadoPago | ✅ Completo |
| Emails de confirmación (cola) | ✅ Completo |
| Boleta PDF (DomPDF) | ✅ Completo |
| Envíos con tracking | ✅ Completo |
| Cupones de descuento | ✅ Completo |
| Credenciales de pago por tienda | ✅ Completo |
| Panel de órdenes admin | ✅ Completo |

---

## Stack

- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Blade + Tailwind CSS
- **Base de datos:** MySQL
- **Pagos:** Transbank SDK v5 + MercadoPago SDK v3 (dx-php)
- **PDF:** barryvdh/laravel-dompdf
- **Colas:** Laravel Queue (driver database)

---

## Arquitectura

```
app/
  Domain/
    Orders/Enums/         OrderStatus (PendingPayment, Paid, Cancelled, Failed, Refunded)
    Payments/
      DTO/                ConfirmedPayment — tipado fuerte entre gateway y service
      Enums/              PaymentStatus
      Gateways/           WebpayGateway, MercadoPagoGateway, FakeGateway, GatewayFactory
      ConfirmPaymentService — idempotencia + lock pesimista
    Shipments/Enums/      ShipmentStatus
    Coupons/Enums/        CouponType

  Models/
    Store, Product, Order, OrderItem, Payment, Shipment, Coupon, StorePaymentConfig
```

Cada tienda tiene su propio `store_id` en todas las tablas. Las credenciales de pago se guardan encriptadas en `store_payment_configs`.

---

## Instalación local

```bash
git clone https://github.com/AlexST360/Multitiendas.git
cd Multitiendas
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Configura las credenciales en `.env`:

```env
# Webpay Plus (credenciales de integración públicas de Transbank)
TRANSBANK_ENV=integration
TRANSBANK_COMMERCE_CODE=597055555532
TRANSBANK_API_KEY=579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C

# MercadoPago (obtén tu access token de sandbox en mercadopago.cl/developers)
MP_ENV=sandbox
MP_ACCESS_TOKEN=tu_access_token_de_sandbox

# Cola de emails (usar database o redis)
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
```

Para procesar la cola de emails:
```bash
php artisan queue:work
```

---

## Prueba de pago Webpay (sandbox)

| Campo | Valor |
|---|---|
| Número de tarjeta | `4051 8856 0044 6623` |
| CVV | `123` |
| Fecha de vencimiento | Cualquier fecha futura |
| RUT | `11.111.111-1` |
| Clave | `123` |

---

## Autor

Desarrollado por [Alex ST](https://github.com/AlexST360)
