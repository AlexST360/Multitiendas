<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\StorefrontController;

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

// Profile
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

Route::middleware(['auth'])->prefix('admin')->group(function () {

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
| Storefront Público (Multi-store real)
|--------------------------------------------------------------------------
|
| Catálogo:
| /s/default
|
| Producto:
| /s/default/p/producto-demo
|
| Checkout (one-step):
| /s/default/checkout
|
*/

Route::get('/s/{store:slug}', [StorefrontController::class, 'index'])
    ->name('storefront.store.index');

Route::get('/s/{store:slug}/p/{product:slug}', [StorefrontController::class, 'show'])
    ->name('storefront.product.show');

// Carrito / Checkout (one-step)
Route::post('/s/{store:slug}/cart/add/{product:slug}', [StorefrontController::class, 'addToCart'])
    ->name('storefront.cart.add');

Route::get('/s/{store:slug}/checkout', [StorefrontController::class, 'checkout'])
    ->name('storefront.checkout');

Route::post('/s/{store:slug}/cart/update', [StorefrontController::class, 'updateCart'])
    ->name('storefront.cart.update');

Route::post('/s/{store:slug}/cart/remove/{productId}', [StorefrontController::class, 'removeFromCart'])
    ->name('storefront.cart.remove');

/*
|--------------------------------------------------------------------------
| Órdenes (MVP) - crear orden desde checkout + thank you público
|--------------------------------------------------------------------------
*/

// Crea la orden (pending_payment) desde el checkout
Route::post('/s/{store:slug}/checkout/place', [StorefrontController::class, 'placeOrder'])
    ->name('storefront.checkout.place');

// Página pública de confirmación (sin exponer id interno)
Route::get('/s/{store:slug}/o/{token}', [StorefrontController::class, 'thankYou'])
    ->name('storefront.order.thankyou');

require __DIR__.'/auth.php';