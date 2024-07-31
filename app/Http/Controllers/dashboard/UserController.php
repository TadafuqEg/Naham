<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Group;
use Illuminate\Validation\Rule;
use Image;
use Str;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\FieldValue;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\AuthError;
use GuzzleHttp\Client;
use File;
use App\Models\Section;


class UserController extends Controller
{
    public function index(Request $request)
    {  

        if ($request->has('search')){

            $all_users = user::where('name', 'LIKE', '%' . $request->search . '%')->orWhere('email', 'LIKE', '%' . $request->search . '%')->paginate(10);
        }else{

            $all_users= user::orderBy('id','desc')->paginate(10);
        } 
        return view('dashboard.users.index',compact('all_users'));

    }

    public function create(){
        if(auth()->check() && auth()->user()->hasRole('super super admin')){
            $roles=Role::all();
        }else{
            $roles=Role::whereIn('name',['admin','user'])->get();
        }
           

        $sections=Section::where('is_active',1)->get();
        return view('dashboard.users.create',compact('roles','sections'));
    }
     
    public function store(Request $request)
    {
        // Validate request input
        if(auth()->check() && auth()->user()->hasRole('super admin')){
            $request->merge(['section' => auth()->user()->section_id]);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => ['required', Rule::in(Role::pluck('id'))],
            'section' => ['required', Rule::in(Section::pluck('id'))],
        ]);
        
    
        // Find the role
        $role = Role::findOrFail($request->role);
    
        // Prepare data for the User model
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'section_id' => $request->section,
            'password' => $request->password,
            'guard' => $role->name,
            'group_id'=>$request->group
        ];
        
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
       //dd($request->email,$request->password);
        // Create the Firebase Auth user
        // $timestamp = new Timestamp(new \DateTime());
        // $dateTime = $timestamp->get()->format('Y-m-d H:i:s');
        // $date = Carbon::now()->setTimezone('Europe/Moscow');
        // $formattedDate = $date->format('F d, Y \a\t g:i:s A \U\T\CP');
        //$timestamp = Carbon::parse($formattedDate)->timestamp;
        // Dump the DateTime object
        
        
        $firebaseUser = $auth->createUserWithEmailAndPassword($request->email, $request->password);
        // Prepare data for Firebase Firestore
        $dateTime = new \DateTime(date('Y-m-d H:i:s'));
        $timestamp = new Timestamp($dateTime);
        $firebaseData = [
            'userid' => $firebaseUser->uid,
            'name' => $request->name,
            'email' => $request->email,
            'created_at'=>$timestamp
             // Note: Consider not storing plain passwords.
        ];
        
        // Create the Firestore document
        $firestore = $factory->createFirestore()->database();
        
        $firestore->collection('users')->document($firebaseUser->uid)->set($firebaseData);
    
        // Create the user in the local database
        $userData['uid'] = $firebaseUser->uid;
        $user = User::create($userData);
    
        // Assign role to the user
        $user->assignRole($role->id);

        // Redirect with success message
        return redirect()->route('users')
            ->with('success', 'User created successfully.');
    }
    
    public function edit($id){
        $user = User::findOrFail($id);
        $user->roles;
        if(auth()->check() && auth()->user()->hasRole('super super admin')){
            $roles=Role::all();
        }else{
            $roles=Role::whereIn('name',['admin','user'])->get();
        }
        $sections=Section::where('is_active',1)->get();
        return view('dashboard.users.edit',compact('roles','user','sections'));
    }
    public function update(Request $request, User $user){
        if(auth()->check() && auth()->user()->hasRole('super admin')){
            $request->merge(['section' => auth()->user()->section_id]);
        }
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id,
            'section' => ['required', Rule::in(Section::pluck('id'))],
            'role' => ['required',Rule::in(Role::pluck('id'))],
        ]);
        $role = Role::find($request->role);
        $data['name']=$request->name;
        $data['email']=$request->email;
        $data['section_id']=$request->section;
        $data['group_id']=$request->group;
        
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
        $firestore = $factory->createFirestore()->database();
        $firebaseUid = $user->uid;
        $auth->updateUser($firebaseUid, [
            
            'email' => $data['email'],
        ]);
        // $dateTime = new \DateTime(date('Y-m-d H:i:s',strtotime($user->created_at)));
        // $timestamp = new Timestamp($dateTime);
        // Update user document in Firestore
        $firestore->collection('users')->document($firebaseUid)->update([
            ['path' => 'name', 'value' => $data['name']],
            ['path' => 'email', 'value' => $data['email']] 
        ]);
        $data['guard']=$role->name;
        $user->update($data);
        $user->syncRoles([$role->id]);
        return redirect()->route('users')
        ->with('success', 'User updated successfully.');
    }
    public function delete($id)
    {   
        
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
        $firestore = $factory->createFirestore()->database();
        $user = User::findOrFail($id);
        $firebaseUid = $user->uid;
        $auth->deleteUser($firebaseUid);
        $firestore->collection('users')->document($firebaseUid)->delete();
        $user->tokens->each(function ($token) {
            $token->delete();
        });
        User::where('id', $id)->delete();
        return redirect('/users');
    }

    function get_section_groups($id){
        $groups=Group::where('section_id',$id)->get();
        return response()->json(['groups' => $groups]);
    }

    function users_location(){
        return view('dashboard.users.users_map');
    }

    function get_all_users(){
        $users=User::whereHas('roles',function($q){
            $q->whereIn('name',['admin','super admin','user']);
            
        })->with('group:id,name','section:id,name')->get();
        return response()->json(['users' => $users]);

    }
}