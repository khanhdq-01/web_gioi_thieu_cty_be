<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserInfoController;
use App\Http\Controllers\CompanyProfileController;

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
    Route::get('/article', [ArticleController::class, 'index']);
    Route::get('/article/{id}', [ArticleController::class, 'show']);
    Route::post('/article', [ArticleController::class, 'store'])->middleware(['ableCreateUpdateProduct']);
    Route::patch('/article/{id}', [ArticleController::class, 'update'])->middleware(['ableCreateUpdateProduct']);
    Route::delete('/article/{id}', [ArticleController::class, 'destroy'])->middleware(['ableCreateUpdateProduct']);

    //User info
    Route::post('/user-info', [UserInfoController::class, 'store']);
    Route::get('/user-info', [UserInfoController::class, 'index']);
    Route::delete('/user-info/{id}', [UserInfoController::class, 'destroy']);

    // Company profile
    Route::post('/company-profile', [CompanyProfileController::class, 'store']);
    Route::get('/company-profile', [CompanyProfileController::class, 'index']);
    Route::delete('/company-profile/{id}', [CompanyProfileController::class, 'destroy']);
});