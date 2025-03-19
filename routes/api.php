<?php

use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function(){
    Route::post('/login',[AuthController::class,'login']);
    Route::get('/me',[AuthController::class,'me'])->middleware(['auth:sanctum']);
    Route::get('/logout',[AuthController::class,'logout'])->middleware(['auth:sanctum']);

});

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/user',[UserController::class, 'store'])->middleware(['ableCreateUser']);
    Route::delete('/product/{id}', [ProductController::class,'destroy'])->middleware(['ableCreateUpdateProduct']);
    Route::get('/product', [ProductController::class,'index']);
    Route::get('/product/{id}', [ProductController::class,'show']);
    Route::post('/product', [ProductController::class, 'store'])->middleware(['ableCreateUpdateProduct']);
    Route::patch('/product/{id}', [ProductController::class, 'update'])->middleware(['ableCreateUpdateProduct']);


    Route::delete('/order/{id}', [OrderController::class, 'destroy'])->middleware(['ableCreateOrder']);
    Route::get('/order/{id}/payment',[OrderController::class,'payment'])->middleware(['ablePayOrder']);
    Route::get('/order',[OrderController::class,'index']);
    Route::post('/order',[OrderController::class,'store'])->middleware(['ableCreateOrder']);
    Route::get('/order/{id}',[OrderController::class,'show']);


    Route::get('/order-report',[OrderController::class,'orderReport'])->middleware(['ableSeeOrderReport']);
    Route::get('/top-dishes', [OrderController::class, 'topDishes']);

});