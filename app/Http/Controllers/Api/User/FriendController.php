<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Friend;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptOrRejectFriendRequestRequest;
use App\Http\Requests\SendFriendRequestRequest;
use App\Http\Resources\FriendRequestsReceivedListResource;
use App\Http\Resources\SentFriendRequestsListResource;
use Illuminate\Http\Request;
use App\Traits\SendFirebase;
use App\Models\Notification;
use App\Events\NotificationDevice;
class FriendController extends Controller
{
    use SendFirebase;

    public function search(Request $request)
    {
        $user = auth('api')->user();
        $friends = Friend::where('sender_id',$user->id)->orWhere('receiver_id',$user->id)->where('status','accepted')->get();
        $friendsIsArray = array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        $users = User::where('id','<>',auth()->user()->id)->whereNotIn('id',$friendsIsArray)->when(isset($request->name_or_email) && $request->name_or_email !=null && $request->name_or_email != '' , function($query) use($request){
            $query->where('name','like','%'.$request->name_or_email.'%')->orWhere('email','like','%'.$request->name_or_email.'%');
        })->paginate(8);
        
        return $this->successWithPagination(data:$users);
    }
    public function unfriendsList()
    {
        $user = auth('api')->user();
        $friends = Friend::where('sender_id',$user->id)->orWhere('receiver_id',$user->id)->get();
        $friendsIsArray = array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        $unfriendList = User::whereNotIn('id',$friendsIsArray)->paginate(8);
        return $this->successWithPagination(data:$unfriendList);
    }

    public function friendRequestsReceivedList()
    {
        $user = auth('api')->user();
        $friends = Friend::with('sender')->where('receiver_id',$user->id)->where('status','pending')->paginate(8);
        
        return $this->successWithPaginationResource(data:FriendRequestsReceivedListResource::collection($friends));
    }

    public function SentFriendRequestsList()
    {
        $user = auth('api')->user();
        $friends = Friend::with('receiver')->where('sender_id',$user->id)->where('status','pending')->paginate(8);
        
        return $this->successWithPaginationResource(data:SentFriendRequestsListResource::collection($friends));
    }
    
    public function friendsList()
    {
        $user = auth('api')->user();
        $friends = Friend::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })->where('status', 'accepted')->get();
        $friendsIsArray = array_merge($friends->pluck('sender_id')->toArray(),$friends->pluck('receiver_id')->toArray());
        $unfriendList = User::whereIn('id',$friendsIsArray)->where('id','<>',$user->id)->paginate(8);
        return $this->successWithPagination(data:$unfriendList);
    }

    public function friendRequest(SendFriendRequestRequest $request)
    {
        $user = auth('api')->user();
        $data = $request->validated();
        $data['sender_id'] = $user->id;
        $data['status'] = 'pending';
        $checkIfIsset = Friend::where(function($query) use($data){
            $query->where('sender_id',$data['sender_id'])->where('receiver_id',$data['receiver_id']);
        })->orWhere(function($query) use($data){
            $query->where('sender_id',$data['receiver_id'])->where('receiver_id',$data['sender_id']);
        })->count();
        if($checkIfIsset > 0)
        {
            return $this->failure('There is a friend request already submitted',409);
        }
        Friend::create($data);
        $receiver = User::find($data['receiver_id']);
        $fcmToken = $receiver->FcmToken??'';
        $this->sendFirebaseNotification(title:'New friend request',notificationBody:[
            'type' => 'new_friend_request',
            'message' => 'you have a new friend request from '.$user->name,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ],token:$fcmToken,message:'you have a new friend request from '.$user->name);
        $data=[
            'title'=>'New friend request',
            'message' => 'you have a new friend request from '.$user->name,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email
        ];
        Notification::create([
            'user_id' => $receiver->id,
            'data' => json_encode($data),
            'type' => 'new_friend_request'
        ]);
        return $this->success('sent successfully');
    }

    public function AcceptOrRejectFriendRequest(AcceptOrRejectFriendRequestRequest $request,$id)
    {
        $user = auth('api')->user();
        $friend = Friend::where('id',$id)->where('receiver_id',$user->id)->firstOrFail();
        if($request->action == 'accept')
        {
            $friend->update(['status'=>'accepted']);
            $sender = User::find($friend->sender_id);
            $fcmToken = $sender->FcmToken??'';
            // $data=[
            //     'type' => 'friend_request_accepted',
            //     'message' => $user->name.' accept your friend request',
            //     'sender_id' => $sender->id,
            //     'sender_name' => $sender->name,
            //     'receiver_id' => $user->id,
            //     'receiver_name' => $user->name,
            // ];
            // $tokens = [];
            // $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
          
            
            //     $device_token=$fcmToken;
                
              
            //     if($device_token){
                   
            //         $mergedTokens = [$device_token];
            //         $serverKey = 'AAAAH30XrvI:APA91bEq5TC1G10d9n40M4ihBdla5VRhWZJRtzHc_Ih8zS1u6yVeHru84DF9ujoYuMM8NrMvtAG1uAn3i2vcbas6ffZVNWETOp9vZOk0FLQnWm4vMb86c1j1EozQ1uxrHLuJcQ8NOoz4';
            //         $fcmData = [
            //             "registration_ids" => $mergedTokens,
            //             "notification" => [
            //                 "title" => 'Friend request accepted',
            //                 "body" => $user->name.' accept your friend request',
                           
            //                 'sound'=>'default'
                           
            //             ],
            //             "data"=>[
            //                 "message" => $user->name.' accept your friend request',
            //                 "data" => $data,
            //                 "title" => 'Friend request accepted',
            //                 "body" => $user->name.' accept your friend request',
                           
            //                 'sound'=>'default'
                           
                            
            //             ],
                        
            //         ];
            //         //$fcmData['to'] = $event->receiver->FcmToken;
                  
            //         $encodedData = json_encode($fcmData);
                   
            //         $headers = [
            //             'Authorization'=>'key=' . $serverKey,
            //             'Content-Type'=>'application/json',
            //         ];
        
            //         $ch = curl_init();
        
            //         curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            //         curl_setopt($ch, CURLOPT_POST, true);
            //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //         curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            //         // Disabling SSL Certificate support temporarly
            //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //         curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
            //         // Execute post
            //         $result = curl_exec($ch);
                   
            //         if ($result === FALSE) {
            //             die('Curl failed: ' . curl_error($ch));
            //         }
                   
            //         // Close connection
            //         curl_close($ch);
            //         // FCM response
            //         // dd($result);
   
                    
                       
                    

            //     }
            $this->sendFirebaseNotification(title:'Friend request accepted',notificationBody:[
                'type' => 'friend_request_accepted',
                'message' => $user->name.' accept your friend request',
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'receiver_id' => $user->id,
                'receiver_name' => $user->name,
            ],token:$fcmToken,message:$user->name.' accept your friend request');
            $data=[
                'title'=>'Friend request accepted',
                'message' => $user->name.' accept your friend request',
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'receiver_id' => $user->id,
                'receiver_name' => $user->name,
            ];
            Notification::create([
                'user_id' => $sender->id,
                'data' => json_encode($data),
                'type' => 'friend_request_accepted'
            ]);
        }else{
            $friend->delete();
        }

        
        return $this->success('operation has been done successfully');
    }
}
