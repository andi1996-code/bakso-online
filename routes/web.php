<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

// Change the root route to point to the index method
Route::get('/', [ProductController::class, 'index'])->name('pages.index');

Route::get('/products', [ProductController::class, 'index'])->name('pages.index');
Route::get('/search', [ProductController::class, 'search'])->name('search');



// Route untuk keranjang
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');
Route::delete('/cart/remove/{productId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::put('/cart/update/{productId}', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
