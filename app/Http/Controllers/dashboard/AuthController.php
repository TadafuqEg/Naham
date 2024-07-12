<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login_view()
    {
        return view('dashboard.auth.login');
    }

    public function login(Request $request)
    {   
        $validator  =   Validator::make($request->all(), [
               
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
               
        ]);
            // dd($request->all());
        if ($validator->fails()) {
           
            return Redirect::back()->withErrors($validator)->withInput($request->all());
        }
        //dd($request->all());
        if (Auth::attempt(['email' => request('email'),'password' => request('password')])){
            $user =  auth()->user();
            $user->is_online = '1';
            $user->lastSignInTime = now()->format('Y-m-d H:i:s');
            $user->save();
            return redirect('/home');
        }else{

            return back()->withErrors(['msg' => 'There is something wrong']);
        }
       
    }


///////////////////////////////////////////  Logout  ///////////////////////////////////////////

    public function logout(Request $request){
        $user = auth()->user();
        $user->is_online='0';
        
        $user->save();
        // Revoke all tokens for the user
        // $user->tokens->each(function ($token) {
        //     $token->delete();
        // });
        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
    
        Auth::logout();
       
       // auth()->guard('admin')->logout();
        return redirect('/login');
    }

    public function home(){
        return view('dashboard.home');
    }
}