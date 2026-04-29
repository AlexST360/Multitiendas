# Multitiendas

Plataforma SaaS para crear y gestionar tiendas online. Cada negocio tiene su propia tienda con catálogo de productos, carrito de compras y pagos integrados — sin que los clientes necesiten crear una cuenta.

Desarrollado con Laravel 12 para el mercado chileno, con soporte para las pasarelas de pago locales.

---

## ¿Qué puede hacer?

**Para el dueño de la tienda**
- Crear y administrar productos con imágenes, precio, stock y SKU
- Activar o desactivar productos desde el panel de administración
- Ver las órdenes que llegan con los datos del cliente

**Para el comprador**
- Navegar el catálogo de la tienda
- Agregar productos al carrito y ajustar cantidades
- Hacer el checkout ingresando solo nombre, email y teléfono
- Pagar con Webpay Plus o MercadoPago
- Recibir una página de confirmación con el detalle de su compra

---

## Pasarelas de pago

| Pasarela | Estado |
|---|---|
| Webpay Plus (Transbank) | Funcional en sandbox |
| MercadoPago Checkout Pro | Funcional en sandbox |

---

## Estado del proyecto

| Módulo | Estado |
|---|---|
| Multi-tienda y catálogo | ✅ Completo |
| Carrito y checkout | ✅ Completo |
| Órdenes | ✅ Completo |
| Pagos (Webpay + MercadoPago) | ✅ Completo |
| Envíos | ⏳ Pendiente |
| Correos de confirmación | ⏳ Pendiente |
| Boleta PDF | ⏳ Pendiente |
| Cupones de descuento | ⏳ Pendiente |
| Credenciales de pago por tienda | ⏳ Pendiente |

---

## Stack

- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Blade + Tailwind CSS
- **Base de datos:** MySQL
- **Pagos:** Transbank SDK v5 + MercadoPago SDK v3

---

## Instalación local

```bash
git clone https://github.com/AlexST360/Multitiendas.git
cd Multitiendas
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Configura las credenciales de Webpay y MercadoPago en `.env`:

```env
TRANSBANK_ENV=integration
TRANSBANK_COMMERCE_CODE=597055555532
TRANSBANK_API_KEY=579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C

MP_ENV=sandbox
MP_ACCESS_TOKEN=tu_access_token_de_sandbox
```

---

## Autor

Desarrollado por [Alex ST](https://github.com/AlexST360)
