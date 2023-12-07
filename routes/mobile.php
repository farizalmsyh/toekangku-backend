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
        Route::group(['prefix' => 'sync'], function () {
            Route::post('fcm-token', [App\Http\Controllers\Api\Mobile\Auth\SyncController::class, 'syncFcmToken']);
            Route::post('location', [App\Http\Controllers\Api\Mobile\Auth\SyncController::class, 'syncLocation']);
        });
    });
    Route::post('register', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'register']);
    Route::post('login', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'login']);
    Route::post('resend-otp', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'resendOTP']);
    Route::post('submit-otp', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'submitOTP']);
    Route::post('forgot-password', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'forgotPassword']);
    Route::post('submit-reset-code', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'submitResetCode']);
    Route::post('reset-password', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'resetPassword']);
});

Route::group(['middleware' => ['auth.sanctum']], function() {
    Route::group(['prefix' => 'seeker'], function () {
        Route::group(['prefix' => 'thread'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'getThread']);
            Route::get('/my', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'getMyThread']);
            Route::post('/create', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'createThread']);
            Route::put('/update', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'updateThread']);
            Route::put('/close', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'closeThread']);
            Route::put('/open', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'openThread']);
            Route::post('/send-interest', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'sendInterest']);
        });
    });
    Route::group(['prefix' => 'worker'], function () {
        Route::group(['prefix' => 'thread'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'getThread']);
            Route::get('/my', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'getMyThread']);
            Route::post('/create', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'createThread']);
            Route::put('/update', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'updateThread']);
            Route::put('/close', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'closeThread']);
            Route::put('/open', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'openThread']);
            Route::post('/send-interest', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'sendInterest']);
        });
    });
});