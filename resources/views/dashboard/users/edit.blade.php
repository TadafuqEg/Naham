@extends('dashboard.layout.app')
@section('title', 'Edit User')
@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4">Edit User</h6>
                <form action="{{ route('update.user',$user) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputEmail3"  name="name" value="{{ old('name',$user->name) }}">
                            @if ($errors->has('name'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('name') }}</p>
                        @endif
                        </div>
                    </div>
                    
                   
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="inputEmail3"  name="email" value="{{ old('email',$user->email) }}">
                            @if ($errors->has('email'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('email') }}</p>
                        @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPassword3" class="col-sm-2 col-form-label">Role</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="floatingSelect" name="role"
                                aria-label="Floating label select example">
                                
                                @foreach($roles as $role)
                                <option value="{{$role->id}}"  @if(old('role')==$role->id || $user->roles->first()->id==$role->id) selected @endif>{{ucwords($role->name)}}</option>
                                @endforeach
                               
                            </select>
                            @if ($errors->has('role'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('role') }}</p>
                        @endif
                        </div>
                    </div>
                    @if(auth()->check() && auth()->user()->hasRole('super super admin'))
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Section</label>
                            <div class="col-sm-10">
                                <select class="form-select" id="sections_select" name="section"
                                    aria-label="Floating label select example" onchange="sections_selection(this);">
                                    <option value="" selected disabled>Select Section</option>
                                    @foreach($sections as $section)
                                    <option value="{{$section->id}}" @if(old('section')==$section->id || $user->section_id==$section->id) selected @endif>{{ucwords($section->name)}}</option>
                                    @endforeach
                                
                                </select>
                                @if ($errors->has('section'))
                                <p class="text-error more-info-err" style="color: red;">
                                    {{ $errors->first('section') }}</p>
                            @endif
                            </div>
                        </div>
                        <div class="row mb-3" id="groups_div">
                            <label class="col-sm-2 col-form-label">Group</label>
                            <div class="col-sm-10">
                                <select class="form-select" id="select_groups" name="group"
                                    aria-label="Floating label select example">
                                    <option value="" selected>Select Group</option>
                                    @foreach($user->section->groups as $group)
                                    <option value="{{$group->id}}" @if(old('group')==$group->id || $user->group_id==$group->id) selected @endif>{{ucwords($group->name)}}</option>
                                    @endforeach
                                
                                </select>
                            </div>
                        </div>
                    @endif
                    @if(auth()->check() && auth()->user()->hasRole('super admin'))
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Group</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="floatingSelect" name="section"
                                aria-label="Floating label select example">
                                <option value="" selected disabled>Select Group</option>
                                @foreach(auth()->user()->section->groups as $group)
                                <option value="{{$group->id}}" @if(old('group')==$group->id || $user->group_id==$group->id) selected @endif>{{ucwords($group->name)}}</option>
                                @endforeach
                            
                            </select>
                        </div>
                    </div>
                @endif
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    function sections_selection(element){
        $.ajax({
    url: '/get_section_groups/' + element.value, // Replace with the actual endpoint URL
    type: 'GET',
    success: function(response) {
        console.log(response.groups);

        // Get the select element
        var selectGroups = $('#select_groups');

        // Clear any existing options (except the placeholder)
        selectGroups.find('option:not(:first)').remove();

        // Append new options
        response.groups.forEach(function(group) {
            var option = $('<option></option>')
                .attr('value', group.id)
                .text(group.name.charAt(0).toUpperCase() + group.name.slice(1));
            
            // If old value matches, set as selected
            if (group.id === "{{ old('group') }}") {
                option.attr('selected', 'selected');
            }

            selectGroups.append(option);
        });

        // Make the groups_div visible and set display to flex
        $('#groups_div').css('display', 'flex');
    },
    error: function(xhr, status, error) {
        // Handle any errors that occur during the AJAX request
        console.log(xhr.responseText);
    }
}); //console.log(element.value);
    }
</script>
@endpush