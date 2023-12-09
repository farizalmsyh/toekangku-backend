<?php

namespace App\Http\Controllers\Api\Mobile\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Experience;
use \Auth;

class ExperienceController extends Controller
{
    public function getExperience(Request $request) {
        $experiences = Experience::where('user_id', Auth::id())->get();
        $response = [
            'experiences' => $experiences,
        ];
        return response()->json(['success' => true, 'data'=> $response]);
    }

    public function getExperienceDetail(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required|numeric'
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $experience = Experience::where('id', $valid['id'])->where('user_id', Auth::id())->first();
        if(!$experience) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pengalaman tidak ditemukan',
	        ], 404);
        }
        $response = [
            'experience' => $experience,
        ];
        return response()->json(['success' => true, 'data'=> $response]);
    }

    public function createExperience(Request $request) {
        $validator = Validator::make($request->all(), [
	        'nama' => 'required',
	        'tanggal' => 'required|date',
	        'deskripsi' => 'required'
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $experience = new Experience;
        $experience->user_id = Auth::id();
        $experience->name = $valid['nama'];
        $experience->date = $valid['tanggal'];
        $experience->description = $valid['deskripsi'];
        $experience->save();

        return response()->json(['success' => true, 'message' => 'Berhasil menambahkan pengalaman']);
    }
    
    public function updateExperience(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required|numeric',
	        'nama' => 'required',
	        'tanggal' => 'required|date',
	        'deskripsi' => 'required'
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $experience = Experience::where('id', $valid['id'])->where('user_id', Auth::id())->first();
        if(!$experience) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pengalaman tidak ditemukan',
	        ], 404);
        }
        $experience->name = $valid['nama'];
        $experience->date = $valid['tanggal'];
        $experience->description = $valid['deskripsi'];
        $experience->save();

        return response()->json(['success' => true, 'message' => 'Berhasil mengubah pengalaman']);
    }

    public function deleteExperience(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required|numeric',
	    ]);
	    if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $experience = Experience::where('id', $valid['id'])->where('user_id', Auth::id())->first();
        if(!$experience) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pengalaman tidak ditemukan',
	        ], 404);
        }
        $experience->delete();

        return response()->json(['success' => true, 'message' => 'Berhasil menghapus pengalaman']);
    }
}
