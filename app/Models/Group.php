<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\CustomDateTimeCast;

class Group extends Model
{
    use HasFactory;
   
    protected $table = 'groups';
    protected $fillable = [
        'is_active',
        'name',
        'code',
        'section_id',
        'active_chat',
        'coordinates'
       
    ];
    protected $allowedSorts = [
       
        'created_at',
        'updated_at'
    ];


    
    public function users()
    {
        return $this->hasMany(User::class,'section_id');
    }
    public function section(){
        return $this->belongsTo(Section::class,'section_id','id');
    }

    
}
