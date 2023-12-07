<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\GeocodeService;

class SyncLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id;
    private $latitude;
    private $longitude;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $latitude, $longitude)
    {
        $this->user_id = $user_id;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $location = GeocodeService::getLocation($this->latitude, $this->longitude);
		$user = User::find($this->user_id);
		$user->location_province = $location['province'];
		$user->location_city = $location['city'];
		$user->location_subdistrict = $location['subdistrict'];
		$user->location_village = $location['village'];
		$user->save();
    }
}
