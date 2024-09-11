<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $fillable = ['sender_id', 'receiver_id', 'message', 'path', 'type', 'location_link'];
    protected $allowedSorts = ['created_at', 'updated_at'];
    protected $guarded = [];

    public function getMessageAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = encrypt($value);
    }

    public function getLocationLinkAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    public function setLocationLinkAttribute($value)
    {
        $this->attributes['location_link'] = encrypt($value);
    }

    public function getPathAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    public function setPathAttribute($value)
    {
        $this->attributes['path'] = encrypt($value);
    }
}
