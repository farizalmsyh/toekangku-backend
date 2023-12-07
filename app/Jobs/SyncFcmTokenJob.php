<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\FcmToken;

class SyncFcmTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id;
    private $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $token)
    {
        $this->user_id = $user_id;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FcmToken::where('token', $this->token)->delete();
        $token = new FcmToken;
        $token->user_id = $this->user_id;
        $token->token = $this->token;
        $token->save();
    }
}
