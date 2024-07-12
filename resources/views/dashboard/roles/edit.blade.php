@extends('dashboard.layout.app')
@section('title', 'Edit Role')
@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4">Edit Role "{{ucwords($role->name)}}"</h6>
                <form action="{{ route('update.role', $role) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputEmail3"  name="name" value="{{ old('name', $role->name) }}">
                            @if ($errors->has('name'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('name') }}</p>
                        @endif
                        </div>
                    </div>
                    
                   
                    <div class="row mb-3">
                        <legend class="col-form-label col-sm-2 pt-0">Permissions</legend>
                        <div class="col-sm-10"style="display: flex;flex-wrap: wrap;">
                            @foreach ($permissions as $permission)
                                <div class="form-check form-switch"style="width: 33%;margin-bottom:1%">
                                    <input class="form-check-input" @if($role->permissions->contains($permission->id) || in_array($permission->name, old('permissions', []))) 
                                    checked 
                                @endif  name="permissions[]" value="{{$permission->name}}" type="checkbox" role="switch"
                                        id="flexSwitchCheckDefault_{{$permission->id}}">
                                    <label class="form-check-label" for="flexSwitchCheckDefault_{{$permission->id}}">{{ucwords($permission->name)}}</label>
                                </div>
                            @endforeach
                            
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