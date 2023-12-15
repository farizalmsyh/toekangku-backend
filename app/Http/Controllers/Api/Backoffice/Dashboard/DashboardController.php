<?php

namespace App\Http\Controllers\Api\Backoffice\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Thread;
use App\Models\User;
use App\Models\Job;

class DashboardController extends Controller
{
    public function getWidget(Request $request) {
        $users = User::whereNotNull('email_verified_at')->where('type', '!=', 'Internal')->count();
        $threads = Thread::where('closed', 0)->where('banned', 0)->count();
        $jobs = Job::where('status', 2)->count();
        $response = [
            'users' => $users,
            'threads' => $threads,
            'jobs' => $jobs,
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function getUserProfession(Request $request) {
        $profesions = User::whereNotNull('email_verified_at')->where('type', '!=', 'Internal')->select('profesion', DB::raw('count(profesion) as count'))->groupBy('profesion')->get(['profession', 'count']);
        $response = [
            'profesions' => $profesions
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function getJob(Request $request) {
        $waiting = Job::where('status', 0)->count();
        $running = Job::where('status', 1)->count();
        $done = Job::where('status', 2)->count();
        $cancel = Job::where('status', 3)->count();
        $response = [
            'waiting' => $waiting,
            'running' => $running,
            'done' => $done,
            'cancel' => $cancel
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function getThread(Request $request) {
        $worker = Thread::where('type', 'Worker')->count();
        $seeker = Thread::where('type', 'Seeker')->count();
        $response = [
            'worker' => $worker,
            'seeker' => $seeker,
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
}
