<?php

namespace App\Http\Controllers\Api\Mobile\Seeker\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserRating as Rating;
use \Auth;

class WorkerController extends Controller
{
    public function getWorker(Request $request) {
        $validator = Validator::make($request->all(), [
	        'limit' => 'required',
	        'offset' => 'required',
            'rating' => 'nullable|numeric',
            'search' => 'nullable',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $query = User::
                    leftJoin(DB::raw('(SELECT user_id, AVG(score) as rating FROM user_ratings GROUP BY user_id) as ratings'), function ($join) {
                        $join->on('users.id', '=', 'ratings.user_id');
                    })
                    ->where('users.type', 'Pekerja');
        if(isset($valid['rating'])) {
            $query = $query->where(DB::raw('COALESCE(ratings.rating, 0)'), '>=', $valid['rating']);
        }
        if(isset($valid['search'])) {
            $search = $valid['search'];
            $query = $query->where(function($query) use ($search) {
                $query->where('users.name', 'ilike', '%'.$search.'%')
                    ->where('users.email', 'ilike', '%'.$search.'%');
            });
        }
        $query = $query->select(
                        'users.*',
                        DB::raw('ratings.rating as rating')
                    )
                    ->groupBy('users.id', 'ratings.rating')
                    ->orderBy('users.created_at', 'DESC');
        $count = $query->get()->count(DB::raw('DISTINCT users.id'));
        $data = $query->limit($valid['limit'])
                    ->offset($valid['offset'])
                    ->get();
        $response = [
            'workers' => $data,
            'count' => $count,
            'limit' => $valid['limit'],
            'offset' => $valid['offset'],
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
}
