<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Http\Controllers\dashboard\AuthController;
use App\Http\Controllers\dashboard\UserController;
use App\Http\Controllers\dashboard\RoleController;
use App\Http\Controllers\dashboard\SectionController;
use App\Http\Controllers\dashboard\GroupController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/rrr', function () {
    dd(env('PUSHER_APP_ID'));
});
Route::get('/', function () {
    
    if(!auth()->user()){
        return redirect('/login');
    }else{
        return redirect('/home');
    }
});

Route::get('/login', [AuthController::class, 'login_view'])->name('login.view');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::group(['middleware' => ['admin']], function () {
    Route::get('/home', [AuthController::class, 'home'])->name('home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    /////////////////////////////////////////
    Route::any('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/create', [UserController::class, 'create'])->name('add.user');
    Route::post('/users/create', [UserController::class, 'store'])->name('create.user');
    Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('edit.user');
    Route::post('/user/update/{user}', [UserController::class, 'update'])->name('update.user');
    Route::get('/user/delete/{id}', [UserController::class, 'delete'])->name('delete.user');
    Route::get('/get_section_groups/{id}', [UserController::class, 'get_section_groups'])->name('get_section_groups');
    ///////////////////////////////////////
    Route::any('/roles', [RoleController::class, 'index'])->name('roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('add.role');
    Route::post('/roles/create', [RoleController::class, 'store'])->name('create.role');
    Route::get('/role/edit/{role}', [RoleController::class, 'edit'])->name('edit.role');
    Route::post('/role/update/{role}', [RoleController::class, 'update'])->name('update.role');
    /////////////////////////////////////////
    Route::any('/sections', [SectionController::class, 'index'])->name('sections');
    Route::get('/sections/create', [SectionController::class, 'create'])->name('add.section');
    Route::post('/sections/create', [SectionController::class, 'store'])->name('create.section');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('edit.section');
    Route::post('/section/update/{section}', [SectionController::class, 'update'])->name('update.section');
    Route::get('/section/delete/{section}', [SectionController::class, 'delete'])->name('delete.section');
    /////////////////////////////////////////
    Route::any('/groups', [GroupController::class, 'index'])->name('groups');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('add.group');
    Route::post('/groups/create', [GroupController::class, 'store'])->name('create.group');
    Route::get('/group/edit/{id}', [GroupController::class, 'edit'])->name('edit.group');
    Route::post('/group/update/{group}', [GroupController::class, 'update'])->name('update.group');
    Route::get('/group/delete/{group}', [GroupController::class, 'delete'])->name('delete.group');
    Route::get('/group/{id}', [GroupController::class, 'change_available_chat'])->name('change_available_chat');
    ///////////////////////////////////////
    Route::get('/users_location', [UserController::class, 'users_location'])->name('users_location');
    Route::get('/get_all_users', [UserController::class, 'get_all_users'])->name('get_all_users');

});


// Route::get('testing',function(){

    
    

//     // Define your two datetime values
//     $datetime1 = Carbon::parse('2023-09-06 10:00:00');
//     $datetime2 = Carbon::parse('2023-09-06 15:30:00');

//     // Calculate the difference in hours and minutes
//     $diff = $datetime1->diff($datetime2);

//     // Extract hours and minutes from the difference
//     $hours = $diff->h;
//     $minutes = $diff->i;

//     // Output the result
//     echo "Total Hours and Minutes Difference: $hours hours and $minutes minutes <br><br><br><br><br>";



//     //-------------------------------------

//     // Sample durations in "h:i" format
//     $durations = ["2:30", "3:15", "1:45"];

//     // Function to convert "h:i" format to minutes
//     function convertToMinutes($time)
//     {
//         list($hours, $minutes) = explode(':', $time);
//         return $hours * 60 + $minutes;
//     }

//     // Convert "h:i" durations to minutes and sum them up
//     $totalMinutes = array_reduce($durations, function ($carry, $duration) {
//         return $carry + convertToMinutes($duration);
//     }, 0);

//     // Calculate total hours and minutes
//     $totalHours = floor($totalMinutes / 60);
//     $totalMinutes %= 60;

//     // Format the result as "h:i"
//     $totalTime = Carbon::createFromTime($totalHours, $totalMinutes)->format('H:i');

//     // Output the result
//     echo "Total Hours: $totalTime";


// });


// Route::get('test-firebase',function(){
//     $fireBase = new App\Traits\SendFirebase();
//     $fireBase->sendFirebaseNotification(notificationBody:['type'=>'you have a new friend request'],token:'d0oA0Tg2TvCLqj_FfvZrU0:APA91bHcLEKf61OscV8-CW8QEQ36PE8iAaGDTSH-usw2R9yYn8Pqw733S4rSVvkU7RQqHayBIwDzITflU1kWd55ff_qhzrKtj0MhrfTb3-QzyRTtMvsLGMMfC-V1Fnd-qAcpLxafguBm');
// });

// Route::get('testing',function(){
//     $user = User::find(208);
//     $userChats = User::whereHas('messages', function ($query) use ($user) {
//         $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
//     })->get();
//     dd($userChats);
// });