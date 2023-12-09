<?php

namespace App\Http\Controllers\Api\Mobile\Resource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\RegionService;

class RegionController extends Controller
{
    public function getProvince(Request $request) {
        $region = new RegionService();
        $data = $region->getProvince();
        $response = [
            'provinces' => $data['result']
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function getCity(Request $request) {
        $validator = Validator::make($request->all(), [
	        'provinsi' => 'required|numeric',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();

        $region = new RegionService();
        $data = $region->getCity($valid['provinsi']);
        $response = [
            'cities' => $data['result']
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function getSubdistrict(Request $request) {
        $validator = Validator::make($request->all(), [
	        'kota' => 'required|numeric',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();

        $region = new RegionService();
        $data = $region->getSubdistrict($valid['kota']);
        $response = [
            'subdistrict' => $data['result']
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function getVillage(Request $request) {
        $validator = Validator::make($request->all(), [
	        'kecamatan' => 'required|numeric',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();

        $region = new RegionService();
        $data = $region->getVillage($valid['kecamatan']);
        $response = [
            'villages' => $data['result']
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
}
