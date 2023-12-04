<?php

namespace App\Http\Controllers\Api\Mobile\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOTPMail;
use App\Mail\SendTokenResetMail;
use App\Models\User;
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
        return response()->json(['success' => true, 'data'=> $user]);
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
        $user = User::where('email', $data['email'])->where('type', '!=', 'Internal')->first();
        if(!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Email atau password salah'], 401);
        }
        $token = $user->createToken('mobile')->plainTextToken;
        $secret = $this->createOTP($user->email, $token);
        $response = [
            'secret' => $secret,
        ];
        return response()->json(['success' => true, 'message' => 'Login berhasil, silahkan tunggu Kode OTP melalui email anda!', 'data' => $response]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
	        'tipe_pengguna' => 'required|string',
	        'nama' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
	        'nik' => 'required',
	        'nomor_telepon' => 'required',
	        'jenis_kelamin' => 'required|string',
	        'tanggal_lahir' => 'required|date',
	        'provinsi' => 'required',
	        'kota' => 'required',
	        'kecamatan' => 'required',
	        'kelurahan' => 'required',
	        'kode_pos' => 'required|numeric',
	        'profesi' => 'required',
	        'tahun_mulai_bekerja' => 'required|numeric',
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $user = new User;
        $user->type = $data['tipe_pengguna'];
        $user->name = $data['nama'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->nik = $data['nik'];
        $user->phone = $data['nomor_telepon'];
        $user->gender = $data['jenis_kelamin'];
        $user->birth_date = $data['tanggal_lahir'];
        $user->address_province = $data['provinsi'];
        $user->address_city = $data['kota'];
        $user->address_subdistrict = $data['kecamatan'];
        $user->address_village = $data['kelurahan'];
        $user->address_zipcode = $data['kode_pos'];
        $user->location_province = $data['provinsi'];
        $user->location_city = $data['kota'];
        $user->location_subdistrict = $data['kecamatan'];
        $user->location_village = $data['kelurahan'];
        $user->profesion = $data['profesi'];
        $user->start_year = $data['tahun_mulai_bekerja'];
        $user->save();

        return response()->json(['success' => true, 'message' => 'Berhasil melakukan pendaftaran']);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password_lama' => 'required|string',
            'password' => 'required|string|confirmed',
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $user = Auth::user();
        if(Hash::check($data['password_lama'], $user->password)) {
            $user->password = Hash::make($data['password']);
            $user->update();
            return response()->json(['success' => true, 'message' => 'Password diubah!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Password salah!'], 401);
        }
    }

    public function logout(Request $request)
    {
        Auth::user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil!']);
    }

    public function resendOTP(Request $request) {
        $validator = Validator::make($request->all(), [
	        'email' => 'required',
	        'secret' => 'required',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $this->recreateOTP($data['email'], $data['secret']);
        return response()->json(['success' => true, 'message' => 'Kode OTP berhasil dikirim!']);
    }

    public function submitOTP(Request $request) {
        $validator = Validator::make($request->all(), [
	        'email' => 'required',
	        'secret' => 'required',
	        'kode' => 'required|numeric',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $otp = OtpCode::where('email', $data['email'])->where('secret', $data['secret'])->where('code', $data['kode'])->where('status', 0)->first();
        if(!$otp) {
            return response()->json(['success' => false, 'message' => 'Kode OTP salah!'], 401);
        }
        $otp->status = 1;
        $otp->save();
        $response = [
            'token' => $otp->token
        ];
        $user = User::where('email', $data['email'])->first();
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return response()->json(['success' => true, 'message' => 'Kode OTP terverifikasi!', 'data' => $response]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
	        'email' => 'required'
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $user = User::where('email', $data['email'])->first();
        if(!$user) {
            return response()->json(['success' => false, 'message' => 'Email tidak ditemukan'], 401);
        }
        $secret = $this->createResetCode($user->email);
        $response = [
            'secret' => $secret,
        ];
        return response()->json(['success' => true, 'message' => 'Silahkan tunggu Kode melalui email anda!', 'data' => $response]);
    }

    public function submitResetCode(Request $request) {
        $validator = Validator::make($request->all(), [
	        'email' => 'required',
	        'secret' => 'required',
	        'kode' => 'required|numeric'
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $reset = ForgotPassword::where('email', $data['email'])->where('secret', $data['secret'])->where('code', $data['kode'])->where('status', 0)->first();
        if(!$reset) {
            return response()->json(['success' => false, 'message' => 'Kode atur ulang password salah!'], 401);
        }
        return response()->json(['success' => true, 'message' => 'Kode atur ulang password terverifikasi!']);
    }
    
    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
	        'email' => 'required',
	        'secret' => 'required',
	        'kode' => 'required|numeric',
	        'password' => 'required|string|confirmed',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $data = $validator->validated();
        $reset = ForgotPassword::where('email', $data['email'])->where('secret', $data['secret'])->where('code', $data['kode'])->where('status', 0)->first();
        if(!$reset) {
            return response()->json(['success' => false, 'message' => 'Kode atur ulang password salah!'], 401);
        }
        $reset->status = 1;
        $reset->save();
        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->save();
        return response()->json(['success' => true, 'message' => 'Berhasil atur ulang password, silahkan Login kembali!']);
    }

    private function createOTP($email, $token) {
        OtpCode::where('email', $email)->update(['status' => 1]);
        $secret = Str::random(30);
        $code = random_int(100000, 999999);
        $otp = new OtpCode;
        $otp->email = $email;
        $otp->token = $token;
        $otp->code = $code;
        $otp->secret = $secret;
        $otp->save();
        SendOTPJob::dispatch($email, $secret);
        // Mail::to($email)->send(new SendOTPMail($email, $secret));
        return $secret;
    }
    
    private function recreateOTP($email, $secret) {
        $code = random_int(100000, 999999);
        $otp = OtpCode::where('email', $email)->where('secret', $secret)->where('status', 0)->latest()->first();
        if($otp) {
            $otp->code = $code;
            $otp->save();
            SendOTPJob::dispatch($email, $secret);
            // Mail::to($email)->send(new SendOTPMail($email, $secret));
        }
    }
    
    private function createResetCode($email) {
        ForgotPassword::where('email', $email)->update(['status' => 1]);
        $secret = Str::random(30);
        $code = random_int(100000, 999999);
        $forgot = new ForgotPassword;
        $forgot->email = $email;
        $forgot->code = $code;
        $forgot->secret = $secret;
        $forgot->save();
        SendTokenResetJob::dispatch($email, $secret);
        // Mail::to($email)->send(new SendTokenResetMail($email, $secret));
        return $secret;
    }
    
    private function recreateResetCode($email, $secret) {
        $code = random_int(100000, 999999);
        $forgot = ForgotPassword::where('email', $email)->where('secret', $secret)->where('status', 0)->latest()->first();
        if($forgot) {
            $forgot->code = $code;
            $forgot->save();
            SendTokenResetJob::dispatch($email, $secret);
            // Mail::to($email)->send(new SendTokenResetMail($email, $secret));
        }
    }
}
