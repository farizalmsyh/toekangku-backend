<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FCMService
{ 
    public static function send($token, $notification, $data = null)
    {
        $config = [];
        $config['to'] = $token;
        $config['notification'] = $notification;
        if($data) {
            $config['data'] = $data;
        }
        Http::acceptJson()->withToken(config('fcm.key'))->post(
            'https://fcm.googleapis.com/fcm/send',
            $config
        );
    }
}