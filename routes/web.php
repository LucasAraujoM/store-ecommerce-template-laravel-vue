<?php

use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('tienda', [ProductController::class, 'index'])->name('products.index');
Route::get('producto/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('carrito', [CartController::class, 'store'])->middleware('throttle:60,1')->name('cart.store');
Route::patch('carrito/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('carrito/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('carrito', [CartController::class, 'clear'])->name('cart.clear');

Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

Route::get('pedido/{order}', [OrderController::class, 'show'])->name('orders.show');

Route::get('seguimiento', [OrderTrackingController::class, 'index'])->name('orders.track');
Route::post('seguimiento', [OrderTrackingController::class, 'search'])->name('orders.track.search');
Route::get('seguimiento/{code}', [OrderTrackingController::class, 'show'])->name('orders.track.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('cuenta/pedidos', [AccountOrderController::class, 'index'])->name('account.orders.index');
    Route::get('cuenta/pedidos/{order}', [AccountOrderController::class, 'show'])->name('account.orders.show');

    Route::get('dashboard', function (Request $request) {
        return $request->user()?->is_admin
            ? redirect()->route('admin.dashboard')
            : redirect()->route('account.orders.index');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
