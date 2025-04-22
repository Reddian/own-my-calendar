<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Own My Calendar') }}</title>
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="{{ secure_asset('css/fonts.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/dashboard.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body>
    <div id="app">
        <!-- Mobile menu toggle button -->
        <button class="mobile-menu-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        
        <!-- Menu overlay for closing when clicking outside -->
        <div class="menu-overlay"></div>
        
        <!-- Mobile header with logo -->
        <div class="mobile-header">
            <div class="logo-container">
                <img src="{{ secure_asset('upload/OwnMyCalendarLogoLight.png') }}" alt="Own My Calendar">
            </div>
        </div>
        
        <!-- Main content -->
        <div class="dashboard-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="logo-container">
                    <img src="{{ secure_asset('upload/OwnMyCalendarLogoLight.png') }}" alt="Own My Calendar">
                </div>
                
                <div class="nav-item {{ request()->is('home') || request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span><a href="{{ route('home') }}">Dashboard</a></span>
                </div>
                
                <div class="nav-item {{ request()->is('calendar*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span><a href="{{ route('calendar') }}">Calendar</a></span>
                </div>
                
                <div class="nav-item {{ request()->is('history*') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span><a href="{{ route('history') }}">History</a></span>
                </div>
                
                <div class="nav-item {{ request()->is('extension*') ? 'active' : '' }}">
                    <i class="fas fa-puzzle-piece"></i>
                    <span><a href="{{ route('extension') }}">Chrome Extension</a></span>
                </div>
                
                <div class="nav-item {{ request()->is('settings*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span><a href="{{ route('settings') }}">Settings</a></span>
                </div>
                
                <div class="nav-item mt-auto">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                    </span>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                @if(!auth()->user()->subscribed() && request()->route()->getName() != 'subscription')
                <div class="subscription-cta">
                    <div class="cta-content">
                        <h4>Upgrade to Premium</h4>
                        <p>You have used {{ auth()->user()->gradesUsed() }} of 3 free grades. Get unlimited grades for just $9/month.</p>
                    </div>
                    <a href="{{ route('subscription') }}" class="btn btn-primary">Upgrade Now</a>
                </div>
                @endif
                
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="app-footer">
                <div class="footer-content">
                    <div class="footer-links">
                        <a href="{{ route('terms') }}">Terms of Service</a>
                        <a href="{{ route('privacy') }}">Privacy Policy</a>
                    </div>
                    <div class="footer-copyright">
                        &copy; {{ date('Y') }} AI Momentum Lab. All rights reserved.
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle functionality with improved behavior
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const menuOverlay = document.querySelector('.menu-overlay');
            
            // Function to open menu
            function openMenu() {
                sidebar.classList.add('active');
                menuOverlay.classList.add('active');
                menuToggle.classList.add('hidden');
            }
            
            // Function to close menu
            function closeMenu() {
                sidebar.classList.remove('active');
                menuOverlay.classList.remove('active');
                menuToggle.classList.remove('hidden');
            }
            
            // Toggle menu when hamburger icon is clicked
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    if (sidebar.classList.contains('active')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });
            }
            
            // Close menu when clicking outside (on overlay)
            if (menuOverlay) {
                menuOverlay.addEventListener('click', function() {
                    closeMenu();
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>
