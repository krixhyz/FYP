<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//order routes
use App\Http\Controllers\OrderController;

Route::post('/order/{product}', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/{order}/checkout', [OrderController::class, 'checkout'])->name('order.checkout');



Route::get('/', [ProductController::class, 'index'])->name('index');

// Protect buy/rent/swap routes:
Route::middleware(['auth'])->group(function () {
    Route::get('/products/{id}/buy', [ProductController::class, 'buy'])->name('products.buy');
    Route::get('/products/{id}/rent', [ProductController::class, 'rent'])->name('products.rent');
    Route::get('/products/{id}/swap', [ProductController::class, 'swap'])->name('products.swap');
});

//cart routes
use App\Http\Controllers\CartController;

Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/place-order', [CartController::class, 'placeFromCart'])->name('orders.placeFromCart');

});


//listing routes

Route::middleware(['auth'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/my-listings', [ProductController::class, 'myListings'])->name('products.myListings');
    Route::patch('/products/{id}/status', [ProductController::class, 'updateStatus'])->name('products.updateStatus');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

});


use App\Http\Controllers\RentalController;

Route::middleware(['auth'])->group(function () {
    // renter side
    Route::get('/rent/{product}', [RentalController::class, 'create'])->name('rental.create');
    Route::post('/rent/{product}', [RentalController::class, 'store'])->name('rental.store');
    Route::get('/rental/checkout/{request}', [RentalController::class, 'checkout'])->name('rental.checkout');

    // owner side (reviewing rental requests)
    Route::get('/rental/request/{request}/review', [RentalController::class, 'review'])->name('rental.review');
    Route::patch('/rental/request/{request}/approve', [RentalController::class, 'approveRequest'])->name('rental.approve');
    Route::patch('/rental/request/{request}/reject', [RentalController::class, 'reject'])->name('rental.reject');


});

//my purchases route
Route::middleware('auth')->group(function () {
    Route::get('/my-purchases', [ProductController::class, 'myPurchases'])->name('products.myPurchases');
});



require __DIR__.'/auth.php';
