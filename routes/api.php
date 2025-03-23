<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function(){
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me'])->middleware(['auth:sanctum']);
    Route::get('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])->group(function(){
    // User APIs
    Route::post('/user', [UserController::class, 'store'])->middleware(['ableCreateUser']);

    // Product APIs
    Route::get('/product', [ProductController::class, 'index']);
    Route::get('/product/{id}', [ProductController::class, 'show']);
    Route::post('/product', [ProductController::class, 'store'])->middleware(['ableCreateUpdateProduct']);
    Route::patch('/product/{id}', [ProductController::class, 'update'])->middleware(['ableCreateUpdateProduct']);
    Route::delete('/product/{id}', [ProductController::class, 'destroy'])->middleware(['ableCreateUpdateProduct']);

    // Cart APIs
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::post('/cart/place-order', [CartController::class, 'placeOrder'])->middleware(['auth:sanctum']);

    // Order APIs
    Route::get('/order', [OrderController::class, 'index']);

    

    // Reports and Statistics
    Route::get('/order-report', [OrderController::class, 'orderReport'])->middleware(['ableSeeOrderReport']);
    Route::get('/top-dishes', [OrderController::class, 'topDishes']);
});