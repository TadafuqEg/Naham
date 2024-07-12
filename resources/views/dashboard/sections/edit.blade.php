@extends('dashboard.layout.app')
@section('title', 'Edit Section')
@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4">Edit Section</h6>
                <form action="{{ route('update.section',$section) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputEmail3"  name="name" value="{{ old('name', $section->name) }}">
                            @if ($errors->has('name'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('name') }}</p>
                        @endif
                        </div>
                    </div>
                    
                   
                    <div class="row mb-3">
                        <legend class="col-form-label col-sm-2 pt-0">Activation</legend>
                        <div class="col-sm-10">
                            
                                <div class="form-check form-switch">
                                    <input class="form-check-input" name="is_active"  type="checkbox" role="switch"
                                        id="flexSwitchCheckDefault" value="1" @if(old('is_active',$section->is_active)) 
                                        checked 
                                    @endif >
                                    <label class="form-check-label" for="flexSwitchCheckDefault">active</label>
                                </div>
                               
                            
                            
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
@endpush