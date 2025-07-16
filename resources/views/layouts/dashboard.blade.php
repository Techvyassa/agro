<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agro - @yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            z-index: 100;
        }
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        .sidebar .nav-link.active {
            color: #2b256c;
            background-color: rgba(43, 37, 108, 0.1);
        }
        .sidebar .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        main {
            padding: 1.5rem;
        }
        .content-header {
            margin-bottom: 1.5rem;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        .card-header {
            border-radius: 0.5rem 0.5rem 0 0 !important;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem;
        }
        .dashboard-icon {
            font-size: 2rem;
            color: #2b256c;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(43, 37, 108, 0.05);
        }
        .btn-primary {
            background-color: #2b256c;
            border-color: #2b256c;
        }
        .btn-primary:hover {
            background-color: #211d53;
            border-color: #211d53;
        }
        .bg-primary {
            background-color: #2b256c !important;
        }
        .text-primary {
            color: #2b256c !important;
        }
        .alert-success {
            background-color: rgba(43, 37, 108, 0.1);
            border-color: rgba(43, 37, 108, 0.2);
            color: #2b256c;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Agro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li> -->
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <input type="hidden" name="redirect" value="/">
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
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link {{ request()->is('sales-orders/upload*') ? 'active' : '' }}" href="{{ route('sales_orders.upload') }}">
                                <i class="fas fa-file-csv"></i> Sales Order CSV Upload
                            </a>
                        </li> -->
                        <li class="nav-item">
    <a class="nav-link {{ request()->is('user/pdfs*') ? 'active' : '' }}" href="{{ route('user.pdfs.index') }}">
        <i class="fas fa-file-pdf"></i>Sales Order PDF Uploads
    </a>
</li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('sales-orders') ? 'active' : '' }}" href="{{ route('sales_orders.index') }}">
                                <i class="fas fa-list"></i> View Sales Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('packlist*') ? 'active' : '' }}" href="{{ route('packlist.index') }}">
                                <i class="fas fa-box"></i> Print Packlist
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('freight-calculator*') ? 'active' : '' }}" href="{{ route('freight.calculator') }}">
                                <i class="fas fa-truck"></i> Calculate Freight
                            </a>
                        </li>
                        <li class="nav-item">
    <a class="nav-link {{ request()->is('track-status*') ? 'active' : '' }}" href="{{ route('track.status') }}">
        <i class="fas fa-truck-loading"></i> Track Status
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
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
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
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @yield('scripts')
</body>
</html>