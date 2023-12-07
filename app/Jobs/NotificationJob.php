<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FCMService;
use App\Models\FCMToken as Token;
use App\Models\User;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id;
    private $title;
    private $message;

    public $tries = 5;
    public $maxExceptions = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $title, $message)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->user_id);
        if($user) {
            $token = Token::where('user_id', $user->id)->pluck('token');
            Mail::to($user->email)->send(new NotificationMail($this->user_id, $this->title, $this->message));
            FCMService::send(
                $token,
                [
                    'title' => $this->title,
                    'body' => $this->message,
                ]
            );
        }
    }
}
