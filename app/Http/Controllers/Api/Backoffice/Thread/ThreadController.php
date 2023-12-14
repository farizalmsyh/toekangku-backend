<?php

namespace App\Http\Controllers\Api\Backoffice\Thread;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Thread;
use App\Models\ThreadInterest as Interest;
use App\Models\ThreadProfesion as Profesion;
use \Auth;

class ThreadController extends Controller
{
    public function getThread(Request $request) {
        $validator = Validator::make($request->all(), [
	        'type' => 'required',
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
        $query = Thread::
                    join('thread_profesions', 'thread_profesions.thread_id', '=', 'threads.id')
                    ->leftJoin('thread_interests', 'thread_interests.thread_id', '=', 'threads.id')
                    ->join('users', 'threads.user_id', '=', 'users.id')
                    ->leftJoin(DB::raw('(SELECT user_id, AVG(score) as rating FROM user_ratings GROUP BY user_id) as ratings'), function ($join) {
                        $join->on('users.id', '=', 'ratings.user_id');
                    })
                    ->where('threads.type', $valid['type']);
        if(isset($valid['search'])) {
            $search = $valid['search'];
            $query = $query->where(function($query) use ($search) {
                $query->where('threads.title', 'ilike', '%'.$search.'%')
                    ->where('threads.description', 'ilike', '%'.$search.'%');
            });
        }
        $query = $query->select(
                        'threads.*',
                        'users.name as user_name',
                        DB::raw('ratings.rating as user_rating'),
                        DB::raw("string_agg(thread_profesions.profesion, ', ') as profesions"),
                        DB::raw('count(thread_interests.id) as total_interest') 
                    )
                    ->groupBy('threads.id', 'users.name', 'ratings.rating')
                    ->orderBy('threads.created_at', 'DESC');
        $count = $query->get()->count(DB::raw('DISTINCT threads.id'));
        $data = $query->limit($valid['limit'])
                    ->offset($valid['offset'])
                    ->get();
        $response = [
            'threads' => $data,
            'count' => $count,
            'limit' => $valid['limit'],
            'offset' => $valid['offset'],
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function changeBannedThread(Request $request) {
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
        $thread = Thread::find($valid['id']);
        if(!$thread) {
            return response()->json([
	            'success' => false,
	            'message' => 'Postingan tidak ditemukan',
	        ], 404);
        }
        $banned = 0;
        if($thread->banned == 0) {
            $banned = 1;
        }
        $thread->banned = $banned;
        $thread->save();
        return response()->json(['success' => true, 'message' => 'Berhasil mengubah blokir']);
    }
}
