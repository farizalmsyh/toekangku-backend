<?php

namespace App\Http\Controllers\Api\Mobile\Worker\Thread;

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
	        'limit' => 'required',
	        'offset' => 'required',
            'profesion' => 'nullable',
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
        $query = Thread::
                    join('thread_profesions', 'thread_profesions.thread_id', '=', 'threads.id')
                    ->leftJoin('thread_interests', 'thread_interests.thread_id', '=', 'threads.id')
                    ->join('users', 'threads.user_id', '=', 'users.id')
                    ->leftJoin(DB::raw('(SELECT user_id, AVG(score) as rating FROM user_ratings GROUP BY user_id) as ratings'), function ($join) {
                        $join->on('users.id', '=', 'ratings.user_id');
                    })
                    ->where('threads.banned', 0)->where('threads.closed', 0)->where('threads.type', 'Seeker')
                    ->where('threads.user_id', '!=', Auth::id());
        if(isset($valid['rating'])) {
            $query = $query->where(DB::raw('COALESCE(ratings.rating, 0)'), '>=', $valid['rating']);
        }
        if(isset($valid['profesion'])) {
            $query = $query->where('thread_profesions.profesion', '=', $valid['profesion']);
        }
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

    public function getDetailThread(Request $request) {
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
        $hostname = config('custom.storage_hostname');
        $data = Thread::
                    join('thread_profesions', 'thread_profesions.thread_id', '=', 'threads.id')
                    ->leftJoin('thread_interests', 'thread_interests.thread_id', '=', 'threads.id')
                    ->join('users', 'threads.user_id', '=', 'users.id')
                    ->leftJoin(DB::raw('(SELECT user_id, AVG(score) as rating FROM user_ratings GROUP BY user_id) as ratings'), function ($join) {
                        $join->on('users.id', '=', 'ratings.user_id');
                    })
                    ->where('threads.id', $valid['id'])
                    ->select(
                        'threads.*',
                        'users.name as user_name',
                        'users.profesion as user_profesion',
                        'users.location_province as user_location_province',
                        'users.location_city as user_location_city',
                        'users.location_subdistrict as user_location_subdistrict',
                        'users.location_village as user_location_village',
                        DB::raw('ratings.rating as user_rating'),
                        DB::raw("string_agg(thread_profesions.profesion, ', ') as profesions"),
                        DB::raw('count(thread_interests.id) as total_interest') 
                    )
                    ->groupBy('threads.id', 'users.name', 'users.profesion', 'users.location_province', 'users.location_city', 'users.location_subdistrict', 'users.location_village', 'ratings.rating')
                    ->first();
        if(!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan tidak ditemukan',
            ], 404);
        }
        $interest = Interest::
                    join('users', 'thread_interests.user_id', '=', 'users.id')
                    ->leftJoin(DB::raw('(SELECT user_id, AVG(score) as rating FROM user_ratings GROUP BY user_id) as ratings'), function ($join) {
                        $join->on('users.id', '=', 'ratings.user_id');
                    })
                    ->where('thread_interests.thread_id', $data->id)
                    ->select(
                        'thread_interests.*',
                        'users.name as user_name',
                        'users.profesion as user_profesion',
                        DB::raw("CASE WHEN users.picture IS NULL THEN NULL ELSE CONCAT('".$hostname."', users.picture) END as user_picture"),
                        'ratings.rating'
                    )
                    ->groupBy('thread_interests.id', 'users.name', 'users.profesion', 'ratings.rating', 'users.picture')
                    ->get();
        $response = [
            'thread' => $data,
            'interest' => $interest
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function getMyThread(Request $request) {
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
        $query = Thread::
                    join('thread_profesions', 'thread_profesions.thread_id', '=', 'threads.id')
                    ->leftJoin('thread_interests', 'thread_interests.thread_id', '=', 'threads.id')
                    ->where('threads.type', 'Worker')
                    ->where('threads.user_id', Auth::id());
        if(isset($valid['search'])) {
            $search = $valid['search'];
            $query = $query->where(function($query) use ($search) {
                $query->where('threads.title', 'ilike', '%'.$search.'%')
                    ->orWhere('threads.description', 'ilike', '%'.$search.'%');
            });
        }
        $query = $query->select(
                        'threads.*',
                        DB::raw("string_agg(thread_profesions.profesion, ', ') as profesions"),
                        DB::raw('count(thread_interests.id) as total_interest') 
                    )
                    ->groupBy('threads.id')
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
    
    public function createThread(Request $request) {
        $validator = Validator::make($request->all(), [
	        'judul' => 'required',
	        'deskripsi' => 'required',
	        'profesi' => 'required|array',
            "profesi.*"  => "required|string|distinct",
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $thread = new Thread;
        $thread->user_id = Auth::id();
        $thread->type = 'Worker';
        $thread->title = $valid['judul'];
        $thread->description = $valid['deskripsi'];
        $thread->save();
        $profesion = $valid['profesi'];
        $data = [];
        for($i = 0; $i < count($profesion); $i++) {
            $body = [
                'thread_id' => $thread->id,
                'profesion' => $profesion[$i]
            ];
            array_push($data, $body);
        }
        Profesion::insert($data);
        return response()->json(['success' => true, 'message' => 'Berhasil membuat postingan']);
    }
    
    public function updateThread(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
	        'judul' => 'required',
	        'deskripsi' => 'required',
	        'profesi' => 'required|array',
            "profesi.*"  => "required|string|distinct",
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
        if($thread->user_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Postingan bukan milik anda',
	        ], 404);
        }
        $thread->title = $valid['judul'];
        $thread->description = $valid['deskripsi'];
        $thread->save();
        $profesion = $valid['profesi'];
        $data = [];
        for($i = 0; $i < count($profesion); $i++) {
            $body = [
                'thread_id' => $thread->id,
                'profesion' => $profesion[$i]
            ];
            array_push($data, $body);
        }
        Profesion::where('thread_id', $thread->id)->delete();
        Profesion::insert($data);
        return response()->json(['success' => true, 'message' => 'Berhasil mengubah postingan']);
    }
    
    public function closeThread(Request $request) {
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
        if($thread->user_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Postingan bukan milik anda',
	        ], 404);
        }
        $thread->closed = 1;
        $thread->save();
        return response()->json(['success' => true, 'message' => 'Berhasil menutup postingan']);
    }

    public function openThread(Request $request) {
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
        if($thread->user_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Postingan bukan milik anda',
	        ], 404);
        }
        $thread->closed = 0;
        $thread->save();
        return response()->json(['success' => true, 'message' => 'Berhasil membuka postingan']);
    }
    
    public function sendInterest(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
	        'pesan' => 'required',
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
        if($thread->user_id == Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Tidak bisa mengirim minat pada postingan anda sendiri',
	        ], 404);
        }
        $interest = new Interest;
        $interest->user_id = Auth::id();
        $interest->thread_id = $thread->id;
        $interest->message = $valid['pesan'];
        $interest->save();
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim minat']);
    }
}
