<?php
namespace App\Http\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Traits\RespondsWithHttpStatus;
use Carbon\Carbon;
use App\Enums\Pagination;
class AttendanceService
{
    use RespondsWithHttpStatus;

    public function index($data){
        
        $dateFrom = ($data['date_from']??Carbon::create(now()->format('Y'), now()->format('m'), 1)->startOfDay());
        $dateTo = ($data['date_to']??Carbon::create(now()->format('Y'), now()->format('m'), 1)->endOfMonth());
        return Attendance::with('logs')->where('user_id',$data['user_id'])->where('date','>=',$dateFrom)->where('date','<=',$dateTo)->orderBy('id','DESC')->paginate(Pagination::PER_PAGE->value);

    }

    public function checkInOut($data){
        $user = null;
        if(isset($data['user_id']) && $data['user_id'] != null && $data['user_id'] != ''){
            $user = User::findOrFail($data['user_id']);
        }else{
            $user = auth()->user();
        }

        $attendance = Attendance::where([
            ['user_id',$user->id],
            ['date',now()->format('Y-m-d')],
        ])->first();
        if($attendance == null){
            $attendance = new Attendance();
            $attendance->day_name = now()->format('l');
            $attendance->date = now()->format('Y-m-d');
            $attendance->user_id = $user->id;
            $attendance->save();
        }
        return $this->storeAttendanceLog($data,$attendance,$data['action_type'],$user);
    }

    public function storeAttendanceLog($data,$attendance,$actionType,$user){
        if(auth()->user()->guard == 'admin'){
            goto saveLog;
        }

        $checkLastLog = AttendanceLog::where('attendance_id',$attendance->id)->latest()->first();
        if($checkLastLog != null && $checkLastLog->action == 'end_time' && $actionType == 'end_time'){
            return $this->failure('please your can\'t check out before check in',status:409);
        }else if($checkLastLog != null && $checkLastLog->action == 'start_time' && $actionType == 'start_time'){
            return $this->failure('please your can\'t check in before check out',status:409);
        }
        
        $attendanceLog = AttendanceLog::where([
            ['attendance_id',$attendance->id],
            ['action',$actionType],
            ['date_time_recording','>=',now()->format('Y-m-d').' 00:00:00'],
            ['date_time_recording','<=',now()->format('Y-m-d').' 23:59:59'],
        ])->first();
        if($attendanceLog != null){
            if($actionType == 'start_time' && !isset($data['update_code'])){
                $countOfAttendanceLogStartTime = AttendanceLog::where([
                    ['attendance_id',$attendance->id],
                    ['action','start_time'],
                    ['created_at','>=',now()->format('Y-m-d').'00:00:00'],
                    ['created_at','<=',now()->format('Y-m-d').'23:59:59'],
                ])->count();
                $countOfAttendanceLogEndTime = AttendanceLog::where([
                    ['attendance_id',$attendance->id],
                    ['action','end_time'],
                    ['created_at','>=',now()->format('Y-m-d').'00:00:00'],
                    ['created_at','<=',now()->format('Y-m-d').'23:59:59'],
                ])->count();
                if($countOfAttendanceLogStartTime == $countOfAttendanceLogEndTime && $countOfAttendanceLogStartTime > 0){
                    return $this->failure('please send update code to check in',status:422);
                }else if($countOfAttendanceLogStartTime == $countOfAttendanceLogEndTime && $data['update_code'] != $user->update_code){
                    return $this->failure('updated code is invalid please try again ',status:422);
                }else{
                    return $this->failure('you already checked in at '.$attendanceLog->date_time_recording,status:409);
                }
            }

        }
        if(($actionType == 'end_time' && now()->format('H:i') < '18:00') && !isset($data['update_code'])){
            return $this->failure('you cant check out before 6:00 PM please contact with admin to send update code',status:422);
        }else if($actionType == 'end_time' && now()->format('H:i') < '18:00' && isset($data['update_code']) && $data['update_code'] != $user->update_code){
            return $this->failure('you cant check out before 6:00 PM please contact with admin to send update code',status:422);
        }
        saveLog:
        $attendanceLog = new AttendanceLog();
        $attendanceLog->date_time_recording = $data['date_time_recording']??now()->format('Y-m-d H:i:s');
        $attendanceLog->action = $actionType;
        $attendanceLog->map_link = $data['map_link'];
        $attendanceLog->attendance_id = $attendance->id;
        $attendanceLog->save();
        $userUpdatedData = [
            'is_online' => $actionType == 'start_time'?1:0
        ];
        if($user->update_code != null){
            $userUpdatedData['update_code'] = null;
        }
        User::find($user->id)->update($userUpdatedData);
        $calculateRageBetweenTwoTimes = null;

        if($actionType == 'end_time'){
            $attendanceLogFirstTime = AttendanceLog::where([['attendance_id',$attendance->id],['action','start_time']])->latest()->first();
            $calculateRageBetweenTwoTimes = $this->calculateRageBetweenTwoTimes($attendanceLogFirstTime->date_time_recording,$attendanceLog->date_time_recording);

            $durations = [$attendance->total_worked_hours,$calculateRageBetweenTwoTimes];
            $total_worked_hours = $this->getTotalTimes($durations);
            $attendance->total_worked_hours = $total_worked_hours;
            $attendance->save();
        }
        return $this->success();
    }

    public function calculateRageBetweenTwoTimes($startTime,$endTime){
        // Define your two datetime values
        $datetime1 = Carbon::parse($startTime);
        $datetime2 = Carbon::parse($endTime);

        // Calculate the difference in hours and minutes
        $diff = $datetime1->diff($datetime2);

        // Extract hours and minutes from the difference
        $hours = $diff->h;
        $minutes = $diff->i;

        return $hours.':'.$minutes;


       
    }

    public function getTotalTimes($durations){
         //-------------------------------------

        // Sample durations in "h:i" format
        // $durations = ["2:30", "3:15", "1:45"];

        // Function to convert "h:i" format to minutes
        function convertToMinutes($time)
        {
            list($hours, $minutes) = explode(':', $time);
            return $hours * 60 + $minutes;
        }

        // Convert "h:i" durations to minutes and sum them up
        $totalMinutes = array_reduce($durations, function ($carry, $duration) {
            
            return $carry + convertToMinutes(Carbon::parse($duration)->format('H:i'));
        }, 0);

        // Calculate total hours and minutes
        $totalHours = floor($totalMinutes / 60);
        $totalMinutes %= 60;
        // Format the result as "h:i"
        $totalTime = Carbon::createFromTime($totalHours, $totalMinutes)->format('H:i');
        // Output the result
        return $totalTime;
    }


    public function statistics($dataRequest){
        $data = [];
        $countOfWorkingDaysWithHours = $this->totalWorkingDaysAndHoursPerMonth($dataRequest);
        $data['total_working_days_per_month'] = $countOfWorkingDaysWithHours['days'];
        $data['total_working_hours_per_month'] = $countOfWorkingDaysWithHours['hours'];
        $totalWorkedHoursPerMonth = $this->totalWorkedHoursPerMonth($dataRequest);
        $data['total_worked_hours_per_month'] = $totalWorkedHoursPerMonth;
        return $data;
    }

    public function totalWorkedHoursPerMonth($data){
        $user = auth()->user();
        if($user == null && $user->guard != 'user'){
            $user = User::findOrFail($data['user_id']);
        }
        $attendanceTotalTimeList = Attendance::query()->
        selectRaw("DATE_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(total_worked_hours))), '%H:%i') as total_time")
        ->where('user_id',$user->id);
        if(isset($data['year']) && $data['year'] != null && $data['year'] != '' && isset($data['month']) && $data['month'] != null && $data['month'] != ''){
            $firstDay = Carbon::create($data['year'], $data['month'], 1)->startOfDay();
            $lastDay = $firstDay->copy()->endOfMonth();
        }else{
            $firstDay = Carbon::create(now()->format('Y'), now()->format('m'), 1)->startOfDay();
            $lastDay = $firstDay->copy()->endOfMonth();
        }
        $startDayFormatted = $firstDay->toDateString(); 
        $lastDayFormatted = $lastDay->toDateString();
        $attendanceTotalTimeList = $attendanceTotalTimeList->where('date','>=',$startDayFormatted)->where('date','<=',$lastDayFormatted);
        $attendanceTotalTimeList = $attendanceTotalTimeList->first()->total_time;
        return  $attendanceTotalTimeList;
    }


    public function totalWorkingDaysAndHoursPerMonth($data)
    {
        // Get the year and month from the request
        $year = $data['year']??now()->format('Y');
        $month = $data['month']??now()->format('m');

        // Create a Carbon instance for the first day of the month
        $startDate = Carbon::createFromDate($year, $month, 1);

        // Initialize a counter for the days
        $count = 0;

        // Loop through each day in the month
        while ($startDate->month == $month) {
            // Check if the current day is not Friday (5) or Saturday (6)
            if ($startDate->dayOfWeek != 5 && $startDate->dayOfWeek != 6) {
                $count++;
            }

            // Move to the next day
            $startDate->addDay();
        }
        return ['days'=>$count ,'hours' => $count * 9] ;
    }

}