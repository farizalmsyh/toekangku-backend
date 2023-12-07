<?php

namespace App\Http\Controllers\Api\Mobile\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SyncFcmTokenJob;
use App\Jobs\SyncLocationJob;
use \Auth;

class SyncController extends Controller
{
    public function syncFcmToken(Request $request) {
        $validator = Validator::make($request->all(), [
	        'token' => 'required',
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
		SyncFcmTokenJob::dispatch(Auth::id(), $valid['token']);
        return response()->json(['success' => true, 'message' => 'Berhasil mensinkron token']);
    }

    public function syncLocation(Request $request) {
        $validator = Validator::make($request->all(), [
	        'latitude' => 'required',
	        'longitude' => 'required',
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
		SyncLocationJob::dispatch(Auth::id(), $valid['latitude'], $valid['longitude']);
        return response()->json(['success' => true, 'message' => 'Berhasil mensinkron lokasi']);
    }
}
