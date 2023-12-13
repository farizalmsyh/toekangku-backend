<?php

namespace App\Http\Controllers\Api\Mobile\General\Help;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Help;
use \Auth;

class HelpController extends Controller
{
    public function sendHelp(Request $request) {
        $validator = Validator::make($request->all(), [
	        'nama' => 'required',
	        'email' => 'required|email',
	        'pesan' => 'required'
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $help = new Help;
        $help->name = $valid['nama'];
        $help->email = $valid['email'];
        $help->message = $valid['pesan'];
        $help->save();

        return response()->json(['success' => true, 'message' => 'Berhasil mengirim laporan']);
    }
}
