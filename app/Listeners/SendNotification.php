<?php

namespace App\Listeners;

use App\Events\NotificationDevice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Models\Notification;

class SendNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */


    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NotificationDevice  $event
     * @return void
     */
    public function handle(NotificationDevice $event)
    {
        //dd($event->user->id.$event->receiver->id);
     
            $data=[
                'type'=>$event->type,
                'message' => $event->message->message,
                'sender_id' => $event->user->id,
                'sender_name' => $event->user->name,
                'sender_email' => $event->user->email,
                'receiver_id' => $event->receiver->id,
                'receiver_name' => $event->receiver->name,
                'receiver_email' => $event->receiver->email,
            ];
            $tokens = [];
            $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
          
            
                $device_token=$event->receiver->FcmToken;
                
              
                if($device_token){
                   
                    $mergedTokens = [$device_token];
                    $serverKey = 'AAAAH30XrvI:APA91bEq5TC1G10d9n40M4ihBdla5VRhWZJRtzHc_Ih8zS1u6yVeHru84DF9ujoYuMM8NrMvtAG1uAn3i2vcbas6ffZVNWETOp9vZOk0FLQnWm4vMb86c1j1EozQ1uxrHLuJcQ8NOoz4';
                    $fcmData = [
                        "registration_ids" => $mergedTokens,
                        "notification" => [
                            "title" => $event->title,
                            "body" => $event->message->message,
                           
                            'sound'=>'default'
                           
                        ],
                        "data"=>[
                            "message" => $event->message->message,
                            "data" => $data,
                            "title" => $event->title,
                            "body" => $event->message->message,
                           
                            'sound'=>'default'
                           
                            
                        ],
                        
                    ];
                    //$fcmData['to'] = $event->receiver->FcmToken;
                  
                    $encodedData = json_encode($fcmData);
                   
                    $headers = [
                        'Authorization'=>'key=' . $serverKey,
                        'Content-Type'=>'application/json',
                    ];
        
                    $ch = curl_init();
        
                    curl_setopt($ch, CURLOPT_URL, $fcmUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    // Disabling SSL Certificate support temporarly
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
                    // Execute post
                    $result = curl_exec($ch);
                   
                    if ($result === FALSE) {
                        die('Curl failed: ' . curl_error($ch));
                    }
                   
                    // Close connection
                    curl_close($ch);
                    // FCM response
                    // dd($result);
   
                    
                        return true;
                    

                }
        
                
        
    }
}