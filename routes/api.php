<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserInfoController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;

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
    Route::post('/article', [ArticleController::class, 'store'])->middleware(['ableCreateUpdateProduct']);
    Route::patch('/article/{id}', [ArticleController::class, 'update'])->middleware(['ableCreateUpdateProduct']);
    Route::delete('/article/{id}', [ArticleController::class, 'destroy'])->middleware(['ableCreateUpdateProduct']);

    //User info
    Route::post('/user-info', [UserInfoController::class, 'store']);
    Route::delete('/user-info/{id}', [UserInfoController::class, 'destroy']);

    // Company profile
    Route::post('/company-profile', [CompanyProfileController::class, 'store']);
    Route::delete('/company-profile/{id}', [CompanyProfileController::class, 'destroy']);


    Route::post('jobs', [JobController::class, 'store']); // Admin thêm công việc
    Route::put('jobs/{id}', [JobController::class, 'update']); // Admin cập nhật công việc
    Route::delete('jobs/{id}', [JobController::class, 'destroy']); // Admin xóa công việc

    Route::get('applications', [ApplicationController::class, 'index']); // Admin xem danh sách ứng viên
    Route::get('applications/{id}', [ApplicationController::class, 'show']); // Admin xem chi tiết ứng viên
    Route::get('applications/{id}/download-cv', [ApplicationController::class, 'downloadCV']); // Admin tải CV
});

Route::get('/article', [ArticleController::class, 'index']);
Route::get('/article/{id}', [ArticleController::class, 'show']);
Route::get('/user-info', [UserInfoController::class, 'index']);
Route::get('/company-profile', [CompanyProfileController::class, 'index']);

Route::get('jobs', [JobController::class, 'index']); // Danh sách công việc
Route::get('jobs/{id}', [JobController::class, 'show']); // Chi tiết công việc
Route::post('applications', [ApplicationController::class, 'store']); // Khách gửi đơn ứng tuyển