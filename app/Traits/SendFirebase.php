<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
trait SendFirebase
{
    public function sendFirebaseNotification($title, $notificationBody , $type=null, $token = null , $tokens = [] ,$message=''){
        
        $SERVER_API_KEY = "AAAAH30XrvI:APA91bEq5TC1G10d9n40M4ihBdla5VRhWZJRtzHc_Ih8zS1u6yVeHru84DF9ujoYuMM8NrMvtAG1uAn3i2vcbas6ffZVNWETOp9vZOk0FLQnWm4vMb86c1j1EozQ1uxrHLuJcQ8NOoz4";

        $data = [
            "notification" => [
                "title" => $title,
                "body" => $message,
                "sound"=> "default"
            ],
            'data' => $notificationBody,
            
        ];

        if ($token == null)
            $data['registration_ids'] = $tokens;
        else
            $data['to'] = $token;
            
        Http::withHeaders([
            'Authorization' => 'key=' . $SERVER_API_KEY,
            'Content-Type' => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', $data);
        
    }
}