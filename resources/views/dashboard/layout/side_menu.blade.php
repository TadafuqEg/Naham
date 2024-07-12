<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-secondary navbar-dark">
        <a href="{{url('/')}}" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary"><i class="fa fa-user-edit"></i> N G</h3>
        </a>
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" src="{{asset('logos/user_logo.png')}}" alt="" style="width: 40px; height: 40px;">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0">{{auth()->user()->name}}</h6>
                <span>{{ucwords(auth()->user()->guard)}}</span>
            </div>
        </div>
        
        <div class="navbar-nav w-100">
            <a href="{{url('/users')}}" class="nav-item nav-link {{ Request::is('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
            @can('create roles')
            <a href="{{url('/roles')}}" class="nav-item nav-link {{ Request::is('roles') ? 'active' : '' }}"><i class="fa fa-users-cog me-2"></i>Roles</a>
            @endcan
            @can('show sections')
            <a href="{{url('/sections')}}" class="nav-item nav-link {{ Request::is('sections') ? 'active' : '' }}"><i class="fa fa-th-large me-2"></i>Sections</a>
            @endcan
            @can('show users')
            <a href="{{url('/users')}}" class="nav-item nav-link {{ Request::is('users') ? 'active' : '' }}"><i class="fa fa-user me-2"></i>Users</a>
            @endcan
            @can('show groups')
            <a href="{{url('/groups')}}" class="nav-item nav-link {{ Request::is('groups') ? 'active' : '' }}"><i class="fa fa-users me-2"></i>Groups</a>
            @endcan
            @can('show users location')
            <a href="{{url('/users_location')}}" class="nav-item nav-link {{ Request::is('users_location') ? 'active' : '' }}"><i class="fa fa-globe me-2"></i>User Location</a>
            @endcan
        </div>
    </nav>
</div>