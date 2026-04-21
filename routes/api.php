<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\User\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/provinces', [LocationController::class, 'provinces']);
Route::get('/cities/{provinceId}', [LocationController::class, 'cities']);

// Category API routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{parentId}/subcategories', [CategoryController::class, 'subcategories']);

Route::middleware('auth:sanctum')->group(function () {
	Route::post('/checkout/calculate', [PaymentController::class, 'calculateCheckout']);
	Route::post('/checkout/pay', [PaymentController::class, 'checkoutPay']);
	Route::post('/payment/verify', [PaymentController::class, 'verifyPayment']);
	Route::post('/orders/{order}', [PaymentController::class, 'orderDetails']);
	Route::get('/transactions/my-history', [PaymentController::class, 'myTransactionHistory']);
});
