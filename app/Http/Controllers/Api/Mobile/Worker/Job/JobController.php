<?php

namespace App\Http\Controllers\Api\Mobile\Worker\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Job;
use App\Models\UserRating as Rating;
use App\Models\User;
use App\Jobs\NotificationJob;
use \Auth;

class JobController extends Controller
{
    public function getJob(Request $request) {
        $validator = Validator::make($request->all(), [
	        'status' => 'required|numeric|in:0,1,2,3',
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
        $query = Job::
                    join(DB::raw('users as seeker'), 'seeker.id', '=','jobs.seeker_id')
                    ->join(DB::raw('users as worker'), 'worker.id', '=','jobs.worker_id')
                    ->leftJoin(DB::raw('(SELECT * FROM user_ratings where sender_id = '.Auth::id().') as rating'), function ($join) {
                        $join->on('jobs.id', '=', 'rating.job_id');
                    })
                    ->where('jobs.status', $valid['status'])
                    ->where('jobs.worker_id', Auth::id());
        if(isset($valid['search'])) {
            $search = $valid['search'];
            $query = $query->where(function($query) use ($search) {
                $query->where('jobs.profesion', 'ilike', '%'.$search.'%')
                    ->orWhere('seeker.name', 'ilike', '%'.$search.'%');
            });
        }
        $query = $query->select(
                        'jobs.*',
                        'rating.score as rating',
                        DB::raw('seeker.name as seeker_name'),
                        DB::raw('worker.name as worker_name')
                    )
                    ->groupBy('jobs.id', 'rating.score', 'worker.name', 'seeker.name')
                    ->orderBy('jobs.created_at');
        $count = $query->get()->count(DB::raw('jobs.id'));
        $data = $query->limit($valid['limit'])
                    ->offset($valid['offset'])
                    ->get();
        $response = [
            'jobs' => $data,
            'count' => $count,
            'limit' => $valid['limit'],
            'offset' => $valid['offset'],
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function confirmOffer(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required',
	        'status' => 'required|numeric|in:1,3',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $job = Job::find($valid['id']);
        if(!$job) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak ditemukan',
	        ], 404);
        }
        if($job->worker_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan bukan milik anda',
	        ], 422);
        }
        $job->status = $valid['status'];
        $job->save();
        $message = 'Anda mendapatkan konfirmasi pekerjaan '.$job->profesion.' oleh '.Auth::user()->name.'. Silahkan cek ToekangKu anda!';
        NotificationJob::dispatch($job->seeker_id, 'Konfirmasi Kerja', $message);
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim konfirmasi pengajuan pekerjaan']);
    }

    public function askCancel(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $job = Job::find($valid['id']);
        if(!$job) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak ditemukan',
	        ], 404);
        }
        if($job->worker_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan bukan milik anda',
	        ], 422);
        }
        $job->canceled_id = Auth::id();
        $job->save();
        $message = 'Anda mendapatkan pengajuan pembatalan pekerjaan '.$job->profesion.' oleh '.Auth::user()->name.'. Silahkan cek ToekangKu anda untuk melakukan konfirmasi pekerjaan!';
        NotificationJob::dispatch($job->seeker_id, 'Pengajuan Pembatalan Kerja', $message);
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim pengajuan pembatalan pekerjaan']);
    }
    
    public function confirmCancel(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $job = Job::find($valid['id']);
        if(!$job) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak ditemukan',
	        ], 404);
        }
        if($job->worker_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan bukan milik anda',
	        ], 422);
        }
        if(!$job->canceled_id) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak dalam pengajuan pembatalan',
	        ], 422);
        }
        $job->status = 3;
        $job->save();
        $message = 'Anda mendapatkan konfirmasi pembatalan pekerjaan '.$job->profesion.' oleh '.Auth::user()->name.'. Silahkan cek ToekangKu anda!';
        NotificationJob::dispatch($job->seeker_id, 'Konfirmasi Pembatalan Kerja', $message);
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim konfirmasi pembatalan pekerjaan']);
    }
    
    public function askDone(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $job = Job::find($valid['id']);
        if(!$job) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak ditemukan',
	        ], 404);
        }
        if($job->worker_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan bukan milik anda',
	        ], 422);
        }
        $job->done_id = Auth::id();
        $job->save();
        $message = 'Anda mendapatkan pengajuan penyelesaian pekerjaan '.$job->profesion.' oleh '.Auth::user()->name.'. Silahkan cek ToekangKu anda untuk melakukan konfirmasi pekerjaan!';
        NotificationJob::dispatch($job->seeker_id, 'Pengajuan Penyelesaian Kerja', $message);
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim pengajuan selesai pekerjaan']);
    }

    public function confirmDone(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $job = Job::find($valid['id']);
        if(!$job) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak ditemukan',
	        ], 404);
        }
        if($job->worker_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan bukan milik anda',
	        ], 422);
        }
        if(!$job->done_id) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak dalam pengajuan selesai',
	        ], 422);
        }
        $job->status = 2;
        $job->save();
        $message = 'Anda mendapatkan konfirmasi penyelesaian pekerjaan '.$job->profesion.' oleh '.Auth::user()->name.'. Silahkan cek ToekangKu anda!';
        NotificationJob::dispatch($job->seeker_id, 'Konfirmasi Penyelesaian Kerja', $message);
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim konfirmasi selesai pekerjaan']);
    }

    public function sendReview(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required',
	        'nilai' => 'required|numeric',
	        'pesan' => 'nullable',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $job = Job::find($valid['id']);
        if(!$job) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan tidak ditemukan',
	        ], 404);
        }
        if($job->worker_id != Auth::id()) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan bukan milik anda',
	        ], 422);
        }
        if($job->status != 2) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan belum selesai',
	        ], 422);
        }
        $check = Rating::where('job_id', $job->id)->where('sender_id', Auth::id())->where('user_id', $job->seeker_id)->first();
        if($check) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pekerjaan telah diberi ulasan',
	        ], 422);
        }
        $rating = new Rating;
        $rating->job_id = $job->id;
        $rating->sender_id = Auth::id();
        $rating->user_id = $job->seeker_id;
        $rating->score = $valid['nilai'];
        $rating->review = $valid['pesan'];
        $rating->save();
        $message = 'Anda mendapatkan ulasan dari '.Auth::user()->name.'. Silahkan cek ToekangKu anda!';
        NotificationJob::dispatch($job->seeker_id, 'Ulasan Kerja', $message);
        return response()->json(['success' => true, 'message' => 'Berhasil mengirim ulasan pekerjaan']);
    }
}
