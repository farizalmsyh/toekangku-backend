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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('job-mail-test', function (Request $request) {
    if($request->email) {
        TestJob::dispatch($request->email);
        return response()->json(['success' => true, 'message' => 'Success send email to '.$request->email]);
    }
    return response()->json(['success' => false, 'message' => 'Fail send email']);
});

Route::post('send-notif', function (Request $request) {
    $token = $request->token;
    $title = $request->title;
    $body = $request->body;
    FCMService::send(
        $token,
        [
            'title' => $title,
            'body' => $body,
        ]
    );
    return response()->json(['success' => false, 'message' => 'Fail send email']);
});

Route::post('run-artisan', function (Request $request) {
    Artisan::call('queue:work --stop-when-empty', []);
});
