<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendRequestsReceivedListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            
            'request_id' =>  $this->id,
            'status' =>  $this->status,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'sender_email' => $this->sender->email,
        ];
    }
}
