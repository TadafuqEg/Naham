<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestCallRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\GroupMessage;
use App\Models\Emergency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\SendFirebase;
use Kreait\Firebase\Factory;
use App\Models\Notification;
use Kreait\Firebase\ServiceAccount;
use Google\Cloud\Firestore\FieldPath;
use Google\Cloud\Firestore\FieldValue;
use App\Events\NotificationDevice;
use App\Events\MessageSent;
class MessageController extends Controller
{
    use SendFirebase;
    public function getMessages($userId)
    {
        $user = auth('api')->user();
        $messages = Message::where(function($query) use($userId ,$user) {
            $query->where('sender_id',$user->id)->where('receiver_id',$userId);
        })->orWhere(function($query) use($userId ,$user) {
            $query->where('receiver_id',$user->id)->where('sender_id',$userId);
        })->orderBy('id','DESC')->paginate(50);
        
        foreach ($messages->items() as $msg) {
            
            $msg->time = $msg->created_at->setTimezone('Africa/Cairo')->format('h:i A');
            
            if($msg->path!=null){
                $msg->path=url($msg->path);
            }
              
            
        }
        return $this->successWithPagination(data:$messages);
    }
    protected function handleFileUpload($request, &$data, &$user)
    {
        $fields = ['image', 'video', 'sheet', 'voice'];
        foreach ($fields as $field) {
            if ($request->file($field)) {
                $invitation_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'), 0, 12);
                $file = $request->file($field);
                $fileName = $user->id . '_' . $invitation_code . '_' . time() . '.' . $file->extension();
                $file->storeAs('public/' . $field . 's', $fileName);
                $path = '/storage/' . $field . 's/' . $fileName;
                $data['path'] = $path;
                $data['type'] = $field;
                
            }
        }
    }
    public function sendMessage(SendMessageRequest $request)
    {  
        $data = $request->validated();
        $user = auth('api')->user();
        $data['sender_id'] = $user->id;
        $this->handleFileUpload($request, $data,$user);

        $message = Message::create($data);
        $message->receiver_id = (int)$message->receiver_id;
    
        $receiver = User::find($data['receiver_id']);
        $fcmToken = $receiver->FcmToken??'';
        $this->sendFirebaseNotification(title:'you have a new message from '.$user->name,notificationBody:[
            'type' => 'new_message',
            'message' => $message->message,
            'sender_id' => $user->id,
            'sender_uid'=>$user->uid,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ],token:$fcmToken,message:$message->message);
        $data=[
            'title'=>'you have a new message from '.$user->name,
            'message' => $message->message,
            'sender_id' => $user->id,
            'sender_uid'=>$user->uid,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ];
        Notification::create([
            'user_id' => $receiver->id,
            'data' => json_encode($data),
            'type' => 'new_message'
        ]);

        broadcast(new MessageSent($message))->toOthers();
        //event(new NotificationDevice($user, $receiver, $message, "new_message", 'you have a new message from ' . $user->name));
    
        
        $message_1=Message::find($message->id);
        if ($message_1->path != null) {
            $message_1->path = url($message_1->path);
        }
        return $this->success(data: $message_1);
    }

    public function requestCall(RequestCallRequest $request)
    {
        $sender = User::find($request->sender_id);
        $receiver = User::find($request->receiver_id);
        $this->sendFirebaseNotification(title:'you have a call from '.$sender->name,notificationBody:[
            'type' => 'call_request',
            'message' => 'you have a call from '.$sender->name,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_email' => $sender->email,
            'sender_uid'=>$sender->uid,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ],token:$receiver->FcmToken,message:'you have a call from '.$sender->name);
        $data=[
            'title'=>'you have a call from '.$sender->name,
            'message' => 'you have a call from '.$sender->name,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_email' => $sender->email,
            'sender_uid'=>$sender->uid,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
        ];
        Notification::create([
            'user_id' => $receiver->id,
            'data' => json_encode($data),
            'type' => 'call_request'
        ]);
        return $this->success();
    }


    public function userChatsList()
    {
        $user = auth()->user();
        // $userChats = User::whereHas('messages', function ($query) use ($user) {
        //     $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        // })->get();
        $receiverIDs = Message::where('sender_id', $user->id)->pluck('receiver_id')->toArray();
        $senderIDs = Message::where('receiver_id', $user->id)->pluck('sender_id')->toArray();
        
        $mergedIDs = array_merge($receiverIDs, $senderIDs);
        
        // If you want to remove duplicates
        $uniqueIDs = array_unique($mergedIDs);
       
        $userChats=User::whereIn('id',$uniqueIDs)->get();
        return $this->success(data:$userChats);
    }

    public function sendMessageGroup(Request $request)
    {  
        $data['message'] = $request->message;
        $user = auth('api')->user();
        
        // ini_set('post_max_size', '500M');
        // ini_set('upload_max_filesize', '500M');
        // ini_set('memory_limit', '1000M');
        //set_time_limit(10000000);
        $this->handleFileUpload($request, $data,$user);
       
        $data['location_link'] = $request->location_link;
        $data['group_id'] = $request->group_id;
        $data['user_id'] = $user->id;
        $admin_message=GroupMessage::create($data);
        // $data['sender_id'] = $user->id;
        $all_users=User::where('id','!=',$user->id)->where('group_id',$request->group_id)->get();
        foreach($all_users as $client){
            // $data['receiver_id']=$client->id;
            // $message = Message::create($data);
            // $message->receiver_id = (int)$message->receiver_id;
            $fcmToken = $client->FcmToken??'';
            $this->sendFirebaseNotification(title:'you have a new message from '.$user->name .' on your group',notificationBody:[
                'type' => 'new_group_message',
                'message' => $admin_message->message,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'sender_email' => $user->email,
                'sender_uid'=>$user->uid,
                'receiver_id' => $client->id,
                'receiver_name' => $client->name,
                'receiver_email' => $client->email,
                'group_id' => $request->group_id,
            ],token:$fcmToken,message:$admin_message->message);
            $data=[
                'title'=>'you have a new message from '.$user->name .' on your group',
                'message' => $admin_message->message,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'sender_email' => $user->email,
                'sender_uid'=>$user->uid,
                'receiver_id' => $client->id,
                'receiver_name' => $client->name,
                'receiver_email' => $client->email,
                'group_id' => $request->group_id,
            ];
            Notification::create([
                'user_id' => $client->id,
                'data' => json_encode($data),
                'type' => 'new_group_message'
            ]);
            //event(new NotificationDevice($user, $client, $message, "new_message", 'you have a new message from ' . $user->name));
        }

        $message_1=GroupMessage::find($admin_message->id);
        if ($message_1->path != null) {
            $message_1->path = url($message_1->path);
        }

        return $this->success(data:$message_1);
    }
    public function all_group_message(Request $request){
        $messages=GroupMessage::where('group_id',$request->group_id)->with('user:id,name')->get();
        return $this->success(data:$messages);
    }

    public function sendEmergencyMessage(Request $request)
    {  
        $data['message'] = $request->message;
        $user = auth('api')->user();
        
        // ini_set('post_max_size', '500M');
        // ini_set('upload_max_filesize', '500M');
        // ini_set('memory_limit', '1000M');
        //set_time_limit(10000000);
        $this->handleFileUpload($request, $data,$user);
       
        $data['location_link'] = $request->location_link;
        $data['group_id'] = $request->group_id;
        $data['user_id'] = $user->id;
        $admin_message=Emergency::create($data);
        // $data['sender_id'] = $user->id;
        $all_users=User::where('id','!=',$user->id)->where('group_id',$request->group_id)->get();
        foreach($all_users as $client){
            // $data['receiver_id']=$client->id;
            // $message = Message::create($data);
            // $message->receiver_id = (int)$message->receiver_id;
            $fcmToken = $client->FcmToken??'';
            $this->sendFirebaseNotification(title:'you have a new mergency message from ' . $user->name ,notificationBody:[
                'type' => 'new_emergency_message',
                'message' => $admin_message->message,
                'message_id' => $admin_message->id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'sender_email' => $user->email,
                'sender_uid'=>$user->uid,
                'receiver_id' => $client->id,
                'receiver_name' => $client->name,
                'receiver_email' => $client->email,
                'group_id' => $request->group_id,
            ],token:$fcmToken,message:$admin_message->message);
            $data=[
                'title'=>'you have a new mergency message from '.$user->name,
                'message' => $admin_message->message,
                'message_id' => $admin_message->id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'sender_email' => $user->email,
                'sender_uid'=>$user->uid,
                'receiver_id' => $client->id,
                'receiver_name' => $client->name,
                'receiver_email' => $client->email,
                'group_id' => $request->group_id,
            ];
            Notification::create([
                'user_id' => $client->id,
                'data' => json_encode($data),
                'type' => 'new_emergency_message'
            ]);
            //event(new NotificationDevice($user, $client, $message, "new_message", 'you have a new message from ' . $user->name));
        }

        $message_1=Emergency::find($admin_message->id);
        if ($message_1->path != null) {
            $message_1->path = url($message_1->path);
        }

        return $this->success(data:$message_1);
    }



}
