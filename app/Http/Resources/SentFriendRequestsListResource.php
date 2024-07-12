<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SentFriendRequestsListResource extends JsonResource
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
            'receiver_id' => $this->receiver->id,
            'receiver_name' => $this->receiver->name,
            'receiver_email' => $this->receiver->email,
        ];
    }
}
