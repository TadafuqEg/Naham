<?php 
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;

class CustomTimeCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }


}