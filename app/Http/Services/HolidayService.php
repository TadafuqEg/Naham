<?php
namespace App\Http\Services;

use App\Models\Holiday;

class HolidayService
{
    public function index($data){
        $holiday = Holiday::query();
        if(isset($data['from']) && $data['from'] != null && $data['from'] !=''){
            $holiday = $holiday->where('date','>=',$data['date_from']);
        }
        if(isset($data['to']) && $data['to'] != null && $data['to'] !=''){
            $holiday = $holiday->where('date','<=',$data['date_to']);
        }
        return $holiday->paginate(8);
    }

    public function store($data){
        Holiday::create($data);
        return true;
    }

    public function update($data,$holiday){
        if(!is_object($holiday)){
            $holiday  = Holiday::findOrFail($holiday);
        }

        $holiday->update($data);
        return true;
    }

    public function destroy($holiday){
        if(!is_object($holiday)){
            $holiday  = Holiday::findOrFail($holiday);
        }
        if(count($holiday->attendances) > 0){
            return false;
        }
        $holiday->delete();
        return true;
    }
}