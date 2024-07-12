<?php
namespace App\Http\Services;

use App\Models\User;
use App\Enums\Pagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
class UserService
{
    public function login($data){
       
        if (auth()->attempt(['email' => $data['email'], 'password' => $data['password'], 'guard' => $data['guard']])) {
            
            //$user =  auth()->user();
            $user = User::with(['group' => function ($query) {
                $query->select('id', 'name');
            }])->where('id',auth()->user()->id)->first();
            $updateData = ['is_online' => 1,'lastSignInTime' => now()->format('Y-m-d H:i:s')];
            if(isset($data['FcmToken']) && $data['FcmToken'] != null && $data['FcmToken'] != '')
            {
                $updateData['FcmToken'] = $data['FcmToken'] ;
            }
            $user->update($updateData);
            $user->access_token = $user->createToken('testing')->plainTextToken;
            $user->with('group:id,name');
            return response(['success' => true,'data'=>$user,'message'=>'The operation has been done'],200);
        }else{
            
            if(User::where('email',$data['email'])->count() > 0){
                return response(['success' => false,'message' => 'Invalid password.'],401);
            }
            return response(['success' => false,'message' => 'Invalid Credentials.'],401);
        }
    }

    public function register($data){
        $data['lastSignInTime']  = now()->format('Y-m-d H:i:s');
        $user =  User::create($data);
        $role = Role::where('name','user')->first();
        $user->assignRole($role->id);
        $user->access_token = $user->createToken('testing')->plainTextToken;
        return response(['success' => true,'data'=>$user,'message'=>'The operation has been done'],200);
    }
    public function changePassword($data){
        $user = User::find(auth()->user()->id);
        $user->password = Hash::make($data['password']);
        $user->save();
        return response(['success' => true,'data'=>$user,'message'=>'The operation has been done'],200);
    }

    public function index(){
        $users = User::where('guard','user')->paginate(Pagination::PER_PAGE->value);
        return $users;
    }

    public function store($data){
        return User::create($data);
    }

    public function update($data,$user){
        if(!is_object($user)){
            $user = User::findOrFail($user);
        }
        if(isset($data['profile_image']))
        {
            $imageName = time().'.'.$data['profile_image']->extension();
    
            $data['profile_image']->move(public_path('profile_images'), $imageName);
            $data['profile_image'] = $imageName;
        }
        $user->update($data);
        return $user;
    }

    public function changeOnlineStatus($is_online,$user)
    {
        if(!is_object($user)){
            $user = User::findOrFail($user);
        }
        $user->update([
            'is_online' => $is_online
        ]);
        return $user;
    }

    public function info($id){
        return  User::where('guard','user')->findOrFail($id);
    }

    // public function generateUpdateCode($userId){
    //     $user = User::where('guard','user')->findOrFail($userId);
    //     $user->update(['update_code'=> substr(Str::uuid(), 0, 5)]);
    //     return $user;
    // }

}