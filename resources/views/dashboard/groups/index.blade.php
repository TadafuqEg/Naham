@extends('dashboard.layout.app')
@section('title', 'Groups')
@section('content')
    <div class="container-fluid pt-4 px-4">
        
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <div style="display: flex;justify-content: space-between;">
                            <h6 class="mb-4">Groups Table</h6>
                            <div style="display: flex;justify-content: space-between; ">
                                <form class="d-none d-md-flex ms-4"style="margin-top:-0.5% !important;" action="{{ route('groups') }}" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input class="form-control bg-dark border-0" type="search"name="search" placeholder="Search">
                                        <button class="btn btn-link text-white" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                                @can('create groups')
                                    <a type="button" href="{{url('/groups/create')}}" class="btn btn-outline-info m-2" style="margin-top:-0.5% !important; float:right;">Create Group</a>
                                @endcan
                            </div>
                           
                        </div>
                        @if(!empty($all_groups) && $all_groups->count())
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Section</th>
                                            <th scope="col">Available Chat</th>
                                            <th scope="col">Activation</th>
                                            
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $counter = $all_groups->firstItem(); // Get the starting index of the current page
                                    @endphp
                                    <tbody>
                                        @foreach($all_groups as $key => $group)
                                        <tr>
                                            <th scope="row">{{$counter++}}</th>
                                            <td>{{ucwords($group->name)}}</td>
                                            <td>{{$group->code}}</td>
                                            <td>{{ucwords($group->section->name)}}</td>
                                            <td><div class="form-check form-switch">
                                                <input onclick="change_available_chat({{$group->id}}, this);" class="form-check-input" name="is_active"  type="checkbox" role="switch"
                                                    id="flexSwitchCheckDefault" value="1" @if($group->active_chat==1) 
                                                    checked 
                                                @endif>
                                                
                                            </div></td>

                                            <td>@if($group->is_active==1) <span class="badge badge-secondary" style="background-color:rgb(50, 134, 50); width:20%;">Active</span> @else <span class="badge badge-secondary" style="background-color:rgb(255,0,0);width:20%;">Unactive</span> @endif</td>
                                            
                                        
                                            <td>
                                                @can('edit groups')
                                                <a href="{{url('/group/edit/'.$group->id)}}" style="margin-right: 1rem;">
                                                    <i style="color:rgb(0,255,0);"class="fa fa-pen" title="Edit"></i>
                                                
                                                </a>
                                                @endcan
                                                @can('delete groups')
                                                <a href="{{route('delete.group',$group)}}">
                                                    <i style="color:rgb(255,0,0);"class="fa fa-trash" title="Delete"></i>
                                                    
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                            <div class="jsgrid-grid-body"style="text-align: center;padding-left:45%;">
                                {!! $all_groups->links("pagination::bootstrap-4") !!}

                            </div>
                        @else
                            
                                <div class="row vh-100 bg-secondary rounded align-items-center justify-content-center mx-0">
                                    <div class="col-md-6 text-center">
                                        <h3>This is blank page</h3>
                                    </div>
                                </div>
                            
                        @endif
                    </div>
                </div>
            </div>
       
    </div>
@endsection
@push('scripts')
    <script>
        function change_available_chat(x,element){
            $.ajax({
                url: '/group/'+ x, // Replace with the actual endpoint URL
                type: 'GET',
                 // Replace with the actual search term or data
                success: function(response) {
                    // if (element.checked) {
                    //     // If element is checked, remove the 'checked' attribute
                    //     element.removeAttribute('checked');
                    // } else {
                    //     // If element is not checked, add the 'checked' attribute
                    //     element.setAttribute('checked', 'checked');
                    // }
                    // Handle the success response here
                    

                },
                error: function(xhr, status, error) {
                    // Handle any errors that occur during the AJAX request
                    console.log(xhr.responseText);
                }
            });
        }
    </script>
@endpush