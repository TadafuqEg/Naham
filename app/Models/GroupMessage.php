<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    use HasFactory;
    protected $table = 'group_messages';
    protected $fillable = [
        
        'message',
        'path',
        'type',
        'location_link',
        'group_id',
        'user_id'
    ];
    protected $allowedSorts = [
       
        'created_at',
        'updated_at'
    ];
    protected $guarded = [];
    public function group(){
        return $this->belongsTo(Group::class,'group_id','id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
