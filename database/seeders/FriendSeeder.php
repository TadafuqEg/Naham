<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Friend;

class FriendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email','user@gmail.com')->first();
        $users = User::where('id','<>',$user->id)->limit(20)->get();
        foreach($users as $userr)
        {
            $senderAndReceiver = [$user->id,$userr->id];
            $status = ['pending','accepted'];
            shuffle($senderAndReceiver);
            shuffle($status);

            Friend::create([
                'sender_id' => $senderAndReceiver[0],
                'receiver_id' => $senderAndReceiver[1],
                'status' => $status[0]
            ]);
        }
        
    }
}
