<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::get('user', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'user']);
        Route::post('logout', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'logout']);
        Route::post('change-password', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'changePassword']);
    });
    Route::post('register', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'register']);
    Route::post('login', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'login']);
    Route::post('resend-otp', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'resendOTP']);
    Route::post('submit-otp', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'submitOTP']);
    Route::post('forgot-password', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'forgotPassword']);
    Route::post('submit-reset-code', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'submitResetCode']);
    Route::post('reset-password', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'resetPassword']);
});