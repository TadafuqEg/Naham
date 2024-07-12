<?php 
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;

class CustomDateTimeCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function set($model, $key, $value, $attributes)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d H:i:s');
    }
}