<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Radiance Eco Lead Management</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #14b8a6;
            --secondary-color: #0f766e;
            --accent-color: #06b6d4;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-color: #f8fafc;
            --dark-color: #0f172a;
            --gray-color: #64748b;
        }
        
        /* Global Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f5f9;
            color: #334155;
        }
        
        .bg-purple {
            background-color: #6f42c1 !important;
            color: white !important;
        }
        
        .card-header.bg-purple {
            color: white !important;
        }
    
        
        /* Navbar Styling */
        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            padding: 0.75rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
            letter-spacing: -0.5px;
        }
        
        .navbar-light .navbar-nav .nav-link {
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            border-radius: 4px;
        }
        
        .navbar-light .navbar-nav .nav-link:hover,
        .navbar-light .navbar-nav .nav-link:focus {
            color: var(--primary-color);
            background-color: rgba(20, 184, 166, 0.05);
        }
        
        .navbar-light .navbar-nav .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(20, 184, 166, 0.1);
        }
        
        /* Card Styling */
        .card {
            border: none;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
            border-radius: 12px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .card-header {
            font-weight: 600;
            padding: 1rem 1.25rem;
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-header.bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .card-header.bg-success {
            background-color: var(--success-color) !important;
        }
        
        .card-header.bg-warning {
            background-color: var(--warning-color) !important;
        }
        
        .card-header.bg-secondary {
            background-color: var(--gray-color) !important;
        }
        
        /* Button Styling */
        .btn {
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            box-shadow: none;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.2);
        }
        
        /* Table Styling */
        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 0, 0, 0.05);
        }
        
        .table td {
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.05);
        }
        
        /* Activity Thread Styles */
        .activity-item {
            transition: all 0.2s ease;
        }
        
        .activity-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .activity-avatar {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .activity-content {
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }
        
        .activity-actions {
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        
        .activity-item:hover .activity-actions {
            opacity: 1;
        }
        
        /* Card Border Styling */
        .border-left-primary {
            border-left: 4px solid var(--primary-color);
        }
        
        .border-left-success {
            border-left: 4px solid var(--success-color);
        }
        
        .border-left-warning {
            border-left: 4px solid var(--warning-color);
        }
        
        .border-left-danger {
            border-left: 4px solid var(--danger-color);
        }
        
        .border-left-secondary {
            border-left: 4px solid var(--gray-color);
        }
        
        .icon-circle {
            height: 3rem;
            width: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        }
        
        .quick-activity-form {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) inset;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/dashboard') }}">
                    <img src="{{ asset('images/logo.svg') }}" alt="Radiance Eco Logo" height="40">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i> {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                                    <i class="fas fa-users me-1"></i> {{ __('Leads') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('agents.*') ? 'active' : '' }}" href="{{ route('agents.index') }}">
                                    <i class="fas fa-user-tie me-1"></i> {{ __('Agents') }}
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Login') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="navbarDropdown" style="border-radius: 8px; overflow: hidden;">
                                    <div class="px-4 py-2 text-muted">
                                        <small>Signed in as</small>
                                        <div class="fw-bold">{{ Auth::user()->email }}</div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-user-circle me-2 text-primary"></i> {{ __('Profile') }}
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-cog me-2 text-secondary"></i> {{ __('Settings') }}
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
            
            @yield('content')
        </main>
        
        <footer class="footer mt-auto py-3 bg-white border-top">
            <div class="container text-center">
                <span class="text-muted">© {{ date('Y') }} Radiance Eco. All rights reserved.</span>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
