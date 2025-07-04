<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Superadmin Credential</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2b256c;
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
            color: #0d4699;
        }
        .login-logo img {
            height: 84px;
            margin-bottom: 15px;
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
            box-shadow: 0 0 0 0.2rem rgba(43, 37, 108, 0.25);
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
            background-color: #211d53;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .form-text {
            color: #777;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="{{ asset('images/login.jpeg') }}" alt="Royal Kissan Logo">
        </div>
        <h3 class="text-center mb-4">Create Superadmin Credential</h3>
        <form method="POST" action="{{ route('superadmin.create-credential') }}">
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
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control with-icon" name="name" placeholder="Name" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control with-icon" name="email" placeholder="Email" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon" name="password" placeholder="Password" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon" name="password_confirmation" placeholder="Confirm Password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-user-plus me-2"></i> Create
            </button>
        </form>
    </div>
</body>
</html> 