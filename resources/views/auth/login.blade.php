@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="m-0">Login</h4>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-4" id="loginTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab" aria-controls="user" aria-selected="true">User</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="false">Admin</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="superadmin-tab" data-bs-toggle="tab" data-bs-target="#superadmin" type="button" role="tab" aria-controls="superadmin" aria-selected="false">Superadmin</button>
                    </li>
                </ul>
                <div class="tab-content" id="loginTabContent">
                    <!-- User Login Form -->
                    <div class="tab-pane fade show active" id="user" role="tabpanel" aria-labelledby="user-tab">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Login</button>
                            </div>
                        </form>
                    </div>
                    <!-- Admin Login Form -->
                    <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                        <form method="POST" action="{{ route('admin.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="admin-email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="admin-email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="admin-password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="admin-remember" name="remember">
                                <label class="form-check-label" for="admin-remember">Remember Me</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Admin Login</button>
                            </div>
                        </form>
                    </div>
                    <!-- Superadmin Login Form -->
                    <div class="tab-pane fade" id="superadmin" role="tabpanel" aria-labelledby="superadmin-tab">
                        <form method="POST" action="{{ route('superadmin.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="superadmin-email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="superadmin-email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="superadmin-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="superadmin-password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="superadmin-remember" name="remember">
                                <label class="form-check-label" for="superadmin-remember">Remember Me</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Superadmin Login</button>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="{{ route('superadmin.create-credential') }}" class="text-primary">Create Credential</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-center">
                    Don't have an account? <a href="{{ route('register') }}" class="text-decoration-none">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS for tab functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection 