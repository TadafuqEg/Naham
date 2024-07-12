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
use App\Models\Section;
use App\Models\Group;
use Illuminate\Validation\Rule;
use Image;
use Str;
use DateTime;
use DateTimeZone;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\AuthError;
use GuzzleHttp\Client;
use File;
use Google\Cloud\Core\Timestamp;
class GroupController extends Controller
{
    public function index(Request $request)
    {  
        $all_groups= Group::orderBy('id','desc');
        if ($request->has('search')){

            $all_groups->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('code', 'LIKE', '%' . $request->search . '%');
            });
        }
        if(auth()->check() && (auth()->user()->hasRole('super admin')||auth()->user()->hasRole('admin'))){
            $all_groups->where('section_id',auth()->user()->section_id);
        }
        $all_groups=$all_groups->paginate(10);
        return view('dashboard.groups.index',compact('all_groups'));

    }
    public function create()
    {
        $sections=Section::where('is_active',1)->get();
        return view('dashboard.groups.create',compact('sections'));
    }
    public function store(Request $request)
    {    
        if(auth()->check() && auth()->user()->hasRole('super admin')){
            $request->merge(['section' => auth()->user()->section_id]);
        }
        $request->validate([
            'name' => 'required|unique:groups,name',
            'code' => 'required|unique:groups,code',
        ]);
        
        if($request->is_active){
            $active='1';
        }else{
            $active='0';
        }

        if($request->active_chat){
            $active_chat='1';
        }else{
            $active_chat='0';
        }
        //$coordinates = json_decode($request->coordinates);
        //dd($coordinates);
        $group = Group::create(['name' => $request->name,'is_active'=> $active,'code'=>$request->code,'section_id'=>$request->section,'active_chat'=>$active_chat,'coordinates'=>$request->coordinates]);
        
        

        return redirect()->route('groups')
            ->with('success', 'Group created successfully.');
    }
    public function edit($id)
    {
       $group=Group::find($id);
       $sections=Section::where('is_active',1)->get();
        return view('dashboard.groups.edit', compact('sections','group'));
    }

    public function update(Request $request,Group $group)
    {   
        if(auth()->check() && auth()->user()->hasRole('super admin')){
            $request->merge(['section' => auth()->user()->section_id]);
        }
        $request->validate([
            'name' => 'required|unique:groups,name,'.$group->id,
            'code' => 'required|unique:groups,code,'.$group->id,
        ]);
       
        if($request->is_active){
            $active='1';
        }else{
            $active='0';
        }

        if($request->active_chat){
            $active_chat='1';
        }else{
            $active_chat='0';
        }
        //  $coordinates = json_decode($request->coordinates);
        //  dd($coordinates);
        $group->update(['name' => $request->name,'is_active'=> $active,'code'=>$request->code,'section_id'=>$request->section,'active_chat'=>$active_chat,'coordinates'=>$request->coordinates]);

        

        return redirect()->route('groups')
            ->with('success', 'Group updated successfully.');
    }
    public function delete(Group $group)
    {
        $group->delete();

        return redirect()->route('groups')
            ->with('success', 'Group deleted successfully.');
    }

    public function change_available_chat($id){
        
        $group=Group::find($id);

        if($group->active_chat=="1"){
            $group->active_chat="0";
        }else{
            $group->active_chat="1";
        }
        $group->save();
        return response()->json(['message' => 'success']);
    }
}