<?php

namespace App\Http\Controllers\Api\Backoffice\Help;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Help;
use \Auth;

class HelpController extends Controller
{
    public function getHelp(Request $request) {
        $validator = Validator::make($request->all(), [
	        'limit' => 'required',
	        'offset' => 'required',
            'search' => 'nullable',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $query = new Help;
        if(isset($valid['search'])) {
            $search = $valid['search'];
            $query = $query->where(function($query) use ($search) {
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->where('email', 'ilike', '%'.$search.'%')
                    ->where('message', 'ilike', '%'.$search.'%');
            });
        }
        $query = $query->orderBy('created_at', 'DESC');
        $count = $query->get()->count(DB::raw('DISTINCT id'));
        $data = $query->limit($valid['limit'])
                    ->offset($valid['offset'])
                    ->get();
        $response = [
            'helps' => $data,
            'count' => $count,
            'limit' => $valid['limit'],
            'offset' => $valid['offset'],
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
}
