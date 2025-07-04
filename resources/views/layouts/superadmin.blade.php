<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agro Superadmin - @yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { min-height: 100vh; overflow-x: hidden; }
        .sidebar { min-height: calc(100vh - 56px); box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); z-index: 100; background-color: #2b256c; }
        .sidebar .nav-link { color: rgba(255,255,255,.75); padding: 0.75rem 1rem; font-weight: 500; }
        .sidebar .nav-link i { margin-right: 0.5rem; width: 20px; text-align: center; }
        .sidebar .nav-link.active { color: #fff; background-color: rgba(255,255,255,.1); }
        .sidebar .nav-link:hover { color: #fff; background-color: rgba(255,255,255,.05); }
        main { padding: 1.5rem; }
        .content-header { margin-bottom: 1.5rem; }
        .card { border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); margin-bottom: 1.5rem; }
        .card-header { border-radius: 0.5rem 0.5rem 0 0 !important; background-color: #fff; border-bottom: 1px solid rgba(0,0,0,0.125); padding: 1rem; }
        .dashboard-icon { font-size: 2rem; color: #212529; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.05); }
        .btn-primary { background-color: #2b256c; border-color: #2b256c; }
        .btn-primary:hover { background-color: #211d53; border-color: #211d53; }
        .bg-primary { background-color: #2b256c !important; }
        .text-primary { color: #2b256c !important; }
        .badge-dark { background-color: #2b256c; color: #fff; }
        .sidebar-heading { color: rgba(255,255,255,.5); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="/superadmin/dashboard">Agro Superadmin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield"></i> Superadmin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('superadmin/dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('superadmin/locations*') ? 'active' : '' }}" href="{{ route('superadmin.locations.index') }}">
                                <i class="fas fa-map-marker-alt"></i> Location Master
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('superadmin/item-masters*') ? 'active' : '' }}" href="{{ route('superadmin.item-masters.index') }}">
                                <i class="fas fa-boxes"></i> Item Master
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('superadmin/users') ? 'active' : '' }}" href="{{ route('superadmin.users.index') }}">
                                <i class="fas fa-users"></i> User List
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link {{ request()->is('location-user/asn-list') ? 'active' : '' }}" href="{{ route('location.asn.list') }}">
                                <i class="fas fa-file-pdf"></i> ASN Uploads List
                            </a>
                        </li> -->
                        {{-- <li class="nav-item">
                            <a class="nav-link {{ request()->is('superadmin/users/create') ? 'active' : '' }}" href="{{ route('superadmin.users.create') }}">
                                <i class="fas fa-user-plus"></i> User Creation
                            </a>
                        </li> --}}
                    </ul>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1">
                        <span>Account</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('superadmin.profile.edit') }}">
                                <i class="fas fa-user-circle"></i> Edit Profile
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <!-- Content Header -->
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-end">
                                    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Home</a></li>
                                    @yield('breadcrumb')
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @yield('scripts')
</body>
</html> 