<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/provinces', [LocationController::class, 'provinces']);
Route::get('/cities/{provinceId}', [LocationController::class, 'cities']);
