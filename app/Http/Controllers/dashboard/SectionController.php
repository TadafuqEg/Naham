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
class SectionController extends Controller
{
    public function index(Request $request)
    {  

        if ($request->has('search')){

            $all_sections = Section::where('name', 'LIKE', '%' . $request->search . '%')->paginate(10);
        }else{

            $all_sections= Section::orderBy('id','desc')->paginate(10);
        } 
        return view('dashboard.sections.index',compact('all_sections'));

    }
    public function create()
    {
        return view('dashboard.sections.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:sections,name',
           
        ]);
       
        if($request->is_active){
            $active='1';
        }else{
            $active='0';
        }
       
        $section = Section::create(['name' => $request->name,'is_active'=> $active]);

        

        return redirect()->route('sections')
            ->with('success', 'Section created successfully.');
    }
    public function edit($id)
    {
       $section=Section::find($id);

        return view('dashboard.sections.edit', compact('section'));
    }

    public function update(Request $request,Section $section)
    {
        $request->validate([
            'name' => 'required|unique:sections,name,'.$section->id,
           
        ]);
       
        if($request->is_active){
            $active='1';
        }else{
            $active='0';
        }
       
        $section->update(['name' => $request->name,'is_active'=> $active]);

        

        return redirect()->route('sections')
            ->with('success', 'Section updated successfully.');
    }
    public function delete(Section $section)
    {
        $section->delete();

        return redirect()->route('sections')
            ->with('success', 'Section deleted successfully.');
    }
}