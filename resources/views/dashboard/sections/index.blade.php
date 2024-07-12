@extends('dashboard.layout.app')
@section('title', 'Sections')
@section('content')
    <div class="container-fluid pt-4 px-4">
        
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <div style="display: flex;justify-content: space-between;">
                            <h6 class="mb-4">Sections Table</h6>
                            <div style="display: flex;justify-content: space-between; ">
                                <form class="d-none d-md-flex ms-4"style="margin-top:-0.5% !important;" action="{{ route('sections') }}" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input class="form-control bg-dark border-0" type="search"name="search" placeholder="Search">
                                        <button class="btn btn-link text-white" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                                @can('create sections')
                                    <a type="button" href="{{url('/sections/create')}}" class="btn btn-outline-info m-2" style="margin-top:-0.5% !important; float:right;">Create Section</a>
                                @endcan
                            </div>
                           
                        </div>
                        @if(!empty($all_sections) && $all_sections->count())
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Activation</th>
                                            
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $counter = $all_sections->firstItem(); // Get the starting index of the current page
                                    @endphp
                                    <tbody>
                                        @foreach($all_sections as $key => $section)
                                        <tr>
                                            <th scope="row">{{$counter++}}</th>
                                            <td>{{ucwords($section->name)}}</td>
                                            <td>@if($section->is_active==1) <span class="badge badge-secondary" style="background-color:rgb(50, 134, 50); width:20%;">Active</span> @else <span class="badge badge-secondary" style="background-color:rgb(255,0,0);width:20%;">Unactive</span> @endif</td>
                                            
                                        
                                            <td>
                                                @can('edit sections')
                                                <a href="{{url('/section/edit/'.$section->id)}}" style="margin-right: 1rem;">
                                                    <i style="color:rgb(0,255,0);"class="fa fa-pen" title="Edit"></i>
                                                
                                                </a>
                                                @endcan
                                                @can('delete sections')
                                                <a href="{{route('delete.section',$section)}}">
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
                                {!! $all_sections->links("pagination::bootstrap-4") !!}

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
@endpush