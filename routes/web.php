<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\FakePaymentController;
use App\Http\Controllers\FakeWebhookController;
use App\Http\Controllers\WebpayInitController;
use App\Http\Controllers\WebpayReturnController;
use App\Http\Controllers\MercadoPagoInitController;
use App\Http\Controllers\MercadoPagoReturnController;
use App\Http\Controllers\MercadoPagoWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::view('/', 'home')->name('home');

// Dashboard
Route::view('/dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Perfil Usuario
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin - Productos
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/products', [ProductController::class, 'index'])
            ->name('admin.products.index');

        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('admin.products.create');

        Route::post('/products', [ProductController::class, 'store'])
            ->name('admin.products.store');

        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
            ->name('admin.products.edit');

        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('admin.products.update');

        Route::post('/products/{product}/toggle-active', [ProductController::class, 'toggleActive'])
            ->name('admin.products.toggleActive');

        Route::post('/products/{product}/images', [ProductController::class, 'storeImage'])
            ->name('admin.products.images.store');

        Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage'])
            ->name('admin.products.images.destroy');
    });

/*
|--------------------------------------------------------------------------
| Storefront Público (Multi-store)
|--------------------------------------------------------------------------
*/

Route::get('/s/{store:slug}', [StorefrontController::class, 'index'])
    ->name('storefront.store.index');

Route::get('/s/{store:slug}/p/{product:slug}', [StorefrontController::class, 'show'])
    ->name('storefront.product.show');

// Carrito / Checkout
Route::post('/s/{store:slug}/cart/add/{product:slug}', [StorefrontController::class, 'addToCart'])
    ->name('storefront.cart.add');

Route::post('/s/{store:slug}/cart/update', [StorefrontController::class, 'updateCart'])
    ->name('storefront.cart.update');

Route::post('/s/{store:slug}/cart/remove/{productId}', [StorefrontController::class, 'removeFromCart'])
    ->name('storefront.cart.remove');

Route::get('/s/{store:slug}/checkout', [StorefrontController::class, 'checkout'])
    ->name('storefront.checkout');

/*
|--------------------------------------------------------------------------
| Órdenes
|--------------------------------------------------------------------------
*/

// Crear orden (pending_payment)
Route::post('/s/{store:slug}/checkout/place', [StorefrontController::class, 'placeOrder'])
    ->name('storefront.checkout.place');

// Thank you público
Route::get('/s/{store:slug}/o/{token}', [StorefrontController::class, 'thankYou'])
    ->name('storefront.order.thankyou');

// Confirmación fake visible (simulación manual)
Route::post('/s/{store:slug}/o/{token}/confirm-fake-payment', [FakePaymentController::class, 'confirm'])
    ->name('storefront.order.confirmFakePayment');

/*
|--------------------------------------------------------------------------
| Webpay Plus — Transbank
|--------------------------------------------------------------------------
*/

// Inicia la transacción y redirige al usuario a Transbank
Route::post('/s/{store:slug}/o/{token}/pay/webpay', [WebpayInitController::class, 'handle'])
    ->name('payments.webpay.init');

// Transbank redirige aquí después del pago con GET (browser redirect, no webhook)
Route::get('/payments/webpay/return', [WebpayReturnController::class, 'handle'])
    ->name('payments.webpay.return');

/*
|--------------------------------------------------------------------------
| MercadoPago — Checkout Pro
|--------------------------------------------------------------------------
*/

// Inicia la preference y redirige al usuario a MercadoPago
Route::post('/s/{store:slug}/o/{token}/pay/mercadopago', [MercadoPagoInitController::class, 'handle'])
    ->name('payments.mp.init');

// MP redirige aquí después del pago (GET con payment_id, status, external_reference)
Route::get('/payments/mercadopago/return', [MercadoPagoReturnController::class, 'handle'])
    ->name('payments.mp.return');

// Webhook server-to-server de MP (CSRF excluido en bootstrap/app.php)
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('payments.mp.webhook');

/*
|--------------------------------------------------------------------------
| Webhook Fake (Server-to-Server)
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/fake', [FakeWebhookController::class, 'handle'])
    ->name('webhooks.fake');

require __DIR__.'/auth.php';