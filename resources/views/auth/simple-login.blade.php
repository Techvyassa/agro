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
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        
        .login-logo i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .login-logo h3 {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .login-logo p {
            color: #777;
            font-size: 0.9rem;
        }
        
        .login-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .login-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
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
        
        .form-control {
            padding: 12px 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(42, 157, 143, 0.25);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            border-right: none;
        }
        
        .form-control.with-icon {
            border-left: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #238c7f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .login-form {
            display: none;
        }
        
        .login-form.active {
            display: block;
        }
        
        .form-text {
            color: #777;
            font-size: 0.85rem;
        }
        
        .form-check-label {
            color: #777;
            font-size: 0.9rem;
        }
        
        .forgot-password {
            color: var(--primary-color);
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #238c7f;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Royal Kissan Logo" style="height: 60px; margin-bottom: 15px;">
            <h3>Royal Kissan </h3>
        </div>
        
        <div class="login-tabs">
            <div id="user-tab" class="login-tab active" onclick="switchTab('user')">User</div>
            <div id="admin-tab" class="login-tab" onclick="switchTab('admin')">Admin</div>
        </div>
        
        <!-- User Login Form -->
        <form id="user-form" action="{{ route('login') }}" method="POST" class="login-form active">
            @csrf
            
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control with-icon" id="user-email" name="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon" id="user-password" name="password" placeholder="Password" required>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="user-remember" name="remember">
                    <label class="form-check-label" for="user-remember">
                        Remember me
                    </label>
                </div>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i> Sign In
            </button>
            
            <!-- <div class="mt-4 text-center">
                <p class="mb-0 form-text">Don't have an account? <a href="{{ route('register') }}" class="text-primary">Register now</a></p>
            </div> -->
        </form>
        
        <!-- Admin Login Form -->
        <form id="admin-form" action="{{ route('admin.login') }}" method="POST" class="login-form">
            @csrf
            
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control with-icon" id="admin-email" name="email" placeholder="Admin Email" value="{{ old('email') }}" required>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon" id="admin-password" name="password" placeholder="Admin Password" required>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="admin-remember" name="remember">
                    <label class="form-check-label" for="admin-remember">
                        Remember me
                    </label>
                </div>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i> Admin Sign In
            </button>
        </form>
    </div>
    
    <script>
        function switchTab(tab) {
            // Update tabs
            document.getElementById('user-tab').classList.remove('active');
            document.getElementById('admin-tab').classList.remove('active');
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Update forms
            document.getElementById('user-form').classList.remove('active');
            document.getElementById('admin-form').classList.remove('active');
            document.getElementById(tab + '-form').classList.add('active');
        }
    </script>
</body>
</html>
