<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agro - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2a9d8f;
            --secondary-color: #264653;
            --accent-color: #e9c46a;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            height: 100vh;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .login-container {
            width: 100%;
            max-width: 1000px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .login-left {
            flex: 1;
            min-width: 400px;
            background: linear-gradient(rgba(38, 70, 83, 0.8), rgba(38, 70, 83, 0.9)), url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-right {
            flex: 1;
            min-width: 400px;
            padding: 40px;
        }
        
        .app-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .app-description {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .features-list {
            padding-left: 0;
            list-style: none;
        }
        
        .features-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .features-list i {
            margin-right: 10px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-size: 12px;
        }
        
        .login-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .login-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .login-tab.active {
            color: var(--primary-color);
        }
        
        .login-tab.active::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .login-form-container {
            position: relative;
            overflow: hidden;
        }
        
        .login-form {
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .form-control {
            border: none;
            border-radius: 0;
            border-bottom: 1px solid #ddd;
            padding: 10px 5px;
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }
        
        .form-label {
            color: #777;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #238c7f;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-check-label {
            color: #777;
            font-size: 0.9rem;
        }
        
        .forgot-password {
            font-size: 0.9rem;
            color: #777;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary-color);
        }
        
        .back-to-home {
            display: inline-flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .back-to-home i {
            margin-right: 8px;
        }
        
        .back-to-home:hover {
            transform: translateX(-5px);
            color: white;
        }
        
        @media (max-width: 992px) {
            .login-left, .login-right {
                min-width: 100%;
            }
            
            .login-left {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-row">
            <div class="login-left">
                <a href="/" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <h1 class="app-title">Agro Platform</h1>
                <p class="app-description">Streamline your agricultural operations with our comprehensive management solution.</p>
                
                <ul class="features-list">
                    <li><i class="fas fa-check"></i> Advanced crop management system</li>
                    <li><i class="fas fa-check"></i> Inventory tracking and control</li>
                    <li><i class="fas fa-check"></i> Weather forecasting integration</li>
                    <li><i class="fas fa-check"></i> Analytics and performance reporting</li>
                    <li><i class="fas fa-check"></i> Mobile access and real-time notifications</li>
                </ul>
            </div>
            
            <div class="login-right">
                <div class="login-tabs">
                    <div id="user-tab" class="login-tab active" onclick="switchTab('user')">User Login</div>
                    <div id="admin-tab" class="login-tab" onclick="switchTab('admin')">Admin Login</div>
                </div>
                
                <div class="login-form-container">
                    <!-- User Login Form -->
                    <form id="user-form" action="{{ route('login') }}" method="POST" class="login-form">
                        @csrf
                        <h4 class="mb-4">Welcome back, User</h4>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label for="user-email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="user-email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="user-password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="user-password" name="password" required>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="user-remember" name="remember">
                                <label class="form-check-label" for="user-remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                        
                        <div class="mt-4 text-center">
                            <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary">Register now</a></p>
                        </div>
                    </form>
                    
                    <!-- Admin Login Form -->
                    <form id="admin-form" action="{{ route('admin.login') }}" method="POST" class="login-form" style="display: none;">
                        @csrf
                        <h4 class="mb-4">Admin Access</h4>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label for="admin-email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="admin-email" name="email" value="{{ old('email') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin-password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="admin-password" name="password" required>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admin-remember" name="remember">
                                <label class="form-check-label" for="admin-remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Admin Sign In</button>
                        
                        <div class="mt-4 text-center">
                            <p class="mb-0">Admin registration is restricted. Contact the system administrator for access.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Update tabs
            document.getElementById('user-tab').classList.remove('active');
            document.getElementById('admin-tab').classList.remove('active');
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Update forms
            document.getElementById('user-form').style.display = 'none';
            document.getElementById('admin-form').style.display = 'none';
            document.getElementById(tab + '-form').style.display = 'block';
        }
    </script>
</body>
</html>
