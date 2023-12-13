<?php

namespace App\Http\Controllers\Api\Backoffice\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Experience;
use App\Models\OtpCode;
use App\Models\ForgotPassword;
use App\Jobs\SendOTPJob;
use App\Jobs\SendTokenResetJob;
use \Auth;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        $user = Auth::user();
        $response = [
            'user' => $user,
        ];
        return response()->json(['success' => true, 'data'=> $response]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
	        'email' => 'required',
	        'password' => 'required',
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $user = User::where('email', $data['email'])->where('type', 'Internal')->first();
        if(!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Email atau password salah'], 401);
        }
        $token = $user->createToken('mobile')->plainTextToken;
        $response = [
            'token' => 'Bearer '.$token,
        ];
        return response()->json(['success' => true, 'message' => 'Login berhasil!', 'data' => $response]);
    }
}
