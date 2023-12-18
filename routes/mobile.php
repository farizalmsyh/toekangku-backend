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
        Route::get('user/detail', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'userDetail']);
        Route::post('logout', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'logout']);
        Route::post('change-password', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'changePassword']);
        Route::post('change-picture', [App\Http\Controllers\Api\Mobile\Auth\AuthController::class, 'changePicture']);
        Route::group(['prefix' => 'sync'], function () {
            Route::post('fcm-token', [App\Http\Controllers\Api\Mobile\Auth\SyncController::class, 'syncFcmToken']);
            Route::post('location', [App\Http\Controllers\Api\Mobile\Auth\SyncController::class, 'syncLocation']);
        });
        Route::group(['prefix' => 'experience'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Auth\ExperienceController::class, 'getExperience']);
            Route::get('detail', [App\Http\Controllers\Api\Mobile\Auth\ExperienceController::class, 'getExperienceDetail']);
            Route::post('create', [App\Http\Controllers\Api\Mobile\Auth\ExperienceController::class, 'createExperience']);
            Route::put('update', [App\Http\Controllers\Api\Mobile\Auth\ExperienceController::class, 'updateExperience']);
            Route::delete('delete', [App\Http\Controllers\Api\Mobile\Auth\ExperienceController::class, 'deleteExperience']);
        });
        Route::group(['prefix' => 'help'], function () {
            Route::post('send', [App\Http\Controllers\Api\Mobile\General\Help\HelpController::class, 'sendHelp']);
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

Route::group(['prefix' => 'resource'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::group(['prefix' => 'region'], function () {
            Route::get('province', [App\Http\Controllers\Api\Mobile\Resource\RegionController::class, 'getProvince']);
            Route::get('city', [App\Http\Controllers\Api\Mobile\Resource\RegionController::class, 'getCity']);
            Route::get('subdistrict', [App\Http\Controllers\Api\Mobile\Resource\RegionController::class, 'getSubdistrict']);
            Route::get('village', [App\Http\Controllers\Api\Mobile\Resource\RegionController::class, 'getVillage']);
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

Route::group(['prefix' => 'general'], function () {
    Route::group(['middleware' => ['auth.sanctum']], function() {
        Route::group(['prefix' => 'room'], function () {
            Route::get('', [App\Http\Controllers\Api\Mobile\General\Chat\ChatController::class, 'getRoom']);
            Route::get('chat', [App\Http\Controllers\Api\Mobile\General\Chat\ChatController::class, 'getChat']);
            Route::post('chat/send', [App\Http\Controllers\Api\Mobile\General\Chat\ChatController::class, 'sendChat']);
        });
    });
});

Route::group(['middleware' => ['auth.sanctum']], function() {
    Route::group(['prefix' => 'seeker'], function () {
        Route::group(['prefix' => 'thread'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'getThread']);
            Route::get('/detail', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'getDetailThread']);
            Route::get('/my', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'getMyThread']);
            Route::post('/create', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'createThread']);
            Route::put('/update', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'updateThread']);
            Route::put('/close', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'closeThread']);
            Route::put('/open', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'openThread']);
            Route::post('/send-interest', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'sendInterest']);
        });
        Route::group(['prefix' => 'job'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'getJob']);
            Route::post('/send-offer', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'sendOffer']);
            Route::put('/ask-cancel', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'askCancel']);
            Route::put('/confirm-cancel', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'confirmCancel']);
            Route::put('/ask-done', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'askDone']);
            Route::put('/confirm-done', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'confirmDone']);
            Route::post('/send-review', [App\Http\Controllers\Api\Mobile\Seeker\Job\JobController::class, 'sendReview']);
        });
        Route::group(['prefix' => 'worker'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Seeker\Worker\WorkerController::class, 'getWorker']);
        });
    });
    Route::group(['prefix' => 'worker'], function () {
        Route::group(['prefix' => 'thread'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'getThread']);
            Route::get('/detail', [App\Http\Controllers\Api\Mobile\Seeker\Thread\ThreadController::class, 'getDetailThread']);
            Route::get('/my', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'getMyThread']);
            Route::post('/create', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'createThread']);
            Route::put('/update', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'updateThread']);
            Route::put('/close', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'closeThread']);
            Route::put('/open', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'openThread']);
            Route::post('/send-interest', [App\Http\Controllers\Api\Mobile\Worker\Thread\ThreadController::class, 'sendInterest']);
        });
        Route::group(['prefix' => 'job'], function () {
            Route::get('/', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'getJob']);
            Route::post('/confirm-offer', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'confirmOffer']);
            Route::put('/ask-cancel', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'askCancel']);
            Route::put('/confirm-cancel', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'confirmCancel']);
            Route::put('/ask-done', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'askDone']);
            Route::put('/confirm-done', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'confirmDone']);
            Route::post('/send-review', [App\Http\Controllers\Api\Mobile\Worker\Job\JobController::class, 'sendReview']);
        });
    });
});