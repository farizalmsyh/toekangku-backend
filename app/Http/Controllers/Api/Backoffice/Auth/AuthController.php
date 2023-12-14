<?php

namespace App\Http\Controllers\Api\Backoffice\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
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

    public function logout(Request $request)
    {
        Auth::user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil!']);
    }

    public function changePicture(Request $request) {
        $validator = Validator::make($request->all(), [
	        'gambar' => 'required|image|max:5240',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
        $disk = Storage::disk('gcs');
		$file = $disk->put('profile-picture', $request->file('gambar'));
        if($file) {
            $path = '/storage/'.$file;
            $user = User::find(Auth::id());
            if($user) {
                $exist = Storage::disk('gcs')->exists($user->picture);
                if($exist) {
                    Storage::disk('gsc')->delete($user->picture);
                }
                $user->picture = $path;
                $user->save();
                return response()->json(['success' => true, 'message' => 'Berhasil mengubah foto profil']);
            }
            return response()->json(['success' => false, 'message' => 'Gagal mengubah foto profil']);
        }
        return response()->json(['success' => false, 'message' => 'Gagal mengubah foto profil']);
    }
}
