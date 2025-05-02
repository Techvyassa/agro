<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agro - Agricultural Management Platform</title>
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
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .navbar {
            padding: 1rem 2rem;
            transition: all 0.3s ease;
        }
        
        .navbar-scrolled {
            background-color: #fff !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            padding: 0.5rem 2rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(38, 70, 83, 0.8), rgba(38, 70, 83, 0.9)), url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0;
            position: relative;
        }
        
        .hero-bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--secondary-color);
            opacity: 0.7;
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-weight: 800;
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
        }
        
        .hero-subtitle {
            font-weight: 400;
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        /* Features Section */
        .features-section {
            padding: 5rem 0;
            background-color: var(--light-color);
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            height: 4px;
            width: 50px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        
        .feature-card {
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            background-color: white;
            border: none;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            background-color: rgba(42, 157, 143, 0.1);
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1.5rem auto;
        }
        
        .feature-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        /* About Section */
        .about-section {
            padding: 5rem 0;
        }
        
        .about-img {
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        /* Call to Action */
        .cta-section {
            padding: 5rem 0;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        /* Footer */
        .footer {
            padding: 3rem 0;
            background-color: var(--dark-color);
            color: white;
        }
        
        .footer-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
            transition: all 0.3s ease;
        }
        
        .footer-link:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: #238c7f;
            border-color: #238c7f;
        }
        
        .btn-secondary {
            background-color: var(--dark-color);
            border-color: var(--dark-color);
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-secondary:hover {
            background-color: #000;
            border-color: #000;
        }
        
        .btn-outline-light {
            border-color: white;
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-outline-light:hover {
            background-color: white;
            color: var(--primary-color);
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 1s ease-in both;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="/">
                <i class="fas fa-leaf me-2"></i>Agro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-primary rounded-pill" href="{{ route('login.page') }}">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center hero-content">
                    <h1 class="hero-title fade-in">
                        Modern Agricultural Management Platform
                    </h1>
                    <p class="hero-subtitle fade-in">
                        Streamline your agricultural operations with our comprehensive management solution built for farmers and agricultural businesses.
                    </p>
                    <div class="d-flex justify-content-center gap-3 fade-in">
                        <a href="{{ route('login.page') }}" class="btn btn-primary btn-lg">
                            User Access
                        </a>
                        <a href="{{ route('login.page') }}" class="btn btn-secondary btn-lg">
                            Admin Access
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h3 class="feature-title">Crop Management</h3>
                        <p class="text-muted">
                            Track and manage your crops from planting to harvest with comprehensive monitoring tools.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h3 class="feature-title">Inventory Control</h3>
                        <p class="text-muted">
                            Efficiently manage your inventory of seeds, fertilizers, equipment, and other agricultural supplies.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Analytics & Reports</h3>
                        <p class="text-muted">
                            Gain valuable insights with detailed analytics and reports to optimize your farming operations.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">Team Management</h3>
                        <p class="text-muted">
                            Coordinate your workforce, assign tasks, and track progress to improve operational efficiency.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Mobile Access</h3>
                        <p class="text-muted">
                            Access your agricultural data anytime, anywhere with our responsive mobile interface.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-cloud-rain"></i>
                        </div>
                        <h3 class="feature-title">Weather Integration</h3>
                        <p class="text-muted">
                            Stay informed with integrated weather forecasts to plan your farming activities effectively.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1515150144380-bca9f1650ed9?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=687&q=80" alt="About Agro" class="img-fluid about-img" />
                </div>
                <div class="col-lg-6">
                    <h2 class="mb-4">About Agro Platform</h2>
                    <p class="lead mb-4">Transforming agricultural management with innovative technology</p>
                    <p>Our platform is designed to revolutionize how farmers and agricultural businesses manage their operations. By combining cutting-edge technology with deep agricultural expertise, we provide a comprehensive solution that helps increase productivity, reduce waste, and maximize profits.</p>
                    <p>Whether you're a small family farm or a large agricultural enterprise, our platform scales to meet your needs with customizable features and flexible options.</p>
                    <div class="mt-4">
                        <a href="#" class="btn btn-primary me-2">Learn More</a>
                        <a href="#contact" class="btn btn-outline-secondary">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">Ready to streamline your agricultural operations?</h2>
            <p class="lead mb-4">Join thousands of satisfied farmers and agricultural businesses who trust Agro for their management needs.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('register') }}" class="btn btn-light btn-lg">Register Now</a>
                <a href="{{ route('login.page') }}" class="btn btn-outline-light btn-lg">Login</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="footer-title">Agro</h5>
                    <p>Agricultural Management Platform that helps farmers and agricultural businesses optimize their operations and increase productivity.</p>
                    <div class="mt-4">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="footer-title">Links</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Home</a></li>
                        <li><a href="#features" class="footer-link">Features</a></li>
                        <li><a href="#about" class="footer-link">About</a></li>
                        <li><a href="#contact" class="footer-link">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="footer-title">Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Blog</a></li>
                        <li><a href="#" class="footer-link">Documentation</a></li>
                        <li><a href="#" class="footer-link">Support</a></li>
                        <li><a href="#" class="footer-link">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="footer-links">
                        <li class="d-flex mb-3">
                            <i class="fas fa-map-marker-alt me-3 mt-1"></i>
                            <span>123 Agricultural Way, Farmington, CA 12345</span>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="fas fa-phone-alt me-3 mt-1"></i>
                            <span>+1 (555) 123-4567</span>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-envelope me-3 mt-1"></i>
                            <span>info@agro-platform.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-4" style="border-color: rgba(255, 255, 255, 0.1);">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-0"> 2024 Agro. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Privacy Policy</a>
                    <a href="#" class="text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add shadow to navbar when scrolled
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
