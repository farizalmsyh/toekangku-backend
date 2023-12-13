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
    });
    Route::post('login', [App\Http\Controllers\Api\Backoffice\Auth\AuthController::class, 'login']);
});