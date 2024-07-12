<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\CustomDateTimeCast;

class Section extends Model
{
    use HasFactory;
   
    protected $table = 'sections';
    protected $fillable = [
        'is_active',
        'name',
       
    ];
    protected $allowedSorts = [
       
        'created_at',
        'updated_at'
    ];


    
    public function users()
    {
        return $this->hasMany(User::class,'section_id');
    }
    public function groups()
    {
        return $this->hasMany(Group::class,'section_id');
    }

    
}
