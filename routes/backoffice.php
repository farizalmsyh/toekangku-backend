<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Jobs\TestJob;
use App\Services\FCMService;

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
        Route::get('user', [App\Http\Controllers\Api\Backoffice\Auth\AuthController::class, 'user']);
        Route::post('logout', [App\Http\Controllers\Api\Backoffice\Auth\AuthController::class, 'logout']);
        Route::post('change-profile', [App\Http\Controllers\Api\Backoffice\Auth\AuthController::class, 'changePicture']);
    });
    Route::post('login', [App\Http\Controllers\Api\Backoffice\Auth\AuthController::class, 'login']);
});

Route::group(['prefix' => 'dashboard'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::get('/widget', [App\Http\Controllers\Api\Backoffice\Dashboard\DashboardController::class, 'getWidget']);
        Route::get('/profession', [App\Http\Controllers\Api\Backoffice\Dashboard\DashboardController::class, 'getUserProfession']);
        Route::get('/job', [App\Http\Controllers\Api\Backoffice\Dashboard\DashboardController::class, 'getJob']);
        Route::get('/thread', [App\Http\Controllers\Api\Backoffice\Dashboard\DashboardController::class, 'getThread']);
    });
});

Route::group(['prefix' => 'thread'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::get('/', [App\Http\Controllers\Api\Backoffice\Thread\ThreadController::class, 'getThread']);
        Route::put('/change-banned', [App\Http\Controllers\Api\Backoffice\Thread\ThreadController::class, 'changeBannedThread']);
    });
});

Route::group(['prefix' => 'help'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::get('/', [App\Http\Controllers\Api\Backoffice\Help\HelpController::class, 'getHelp']);
    });
});

Route::group(['prefix' => 'user'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::get('/', [App\Http\Controllers\Api\Backoffice\User\UserController::class, 'getUser']);
        Route::get('/detail', [App\Http\Controllers\Api\Backoffice\User\UserController::class, 'getUserDetail']);
        Route::post('/create', [App\Http\Controllers\Api\Backoffice\User\UserController::class, 'createUser']);
        Route::put('/update', [App\Http\Controllers\Api\Backoffice\User\UserController::class, 'updateUser']);
        Route::delete('/delete', [App\Http\Controllers\Api\Backoffice\User\UserController::class, 'deleteUser']);
    });
});