<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Own My Calendar') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Styles -->
        <style>
            /* Base styles */
            :root {
                --primary-purple: #7e57ff;
                --primary-purple-dark: #5546ff;
                --accent-blue: #4a89dc;
                --light-gray: #f8f9fa;
                --white: #ffffff;
                --dark-text: #333333;
                --light-text: #666666;
                --event-purple: #f0ebff;
                --event-green: #e6f7f0;
                --event-peach: #fff0e1;
                --event-mint: #e6f7f0;
            }
            
            body {
                font-family: 'Figtree', sans-serif;
                color: var(--dark-text);
                background-color: var(--white);
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }
            
            /* Navbar styles */
            .navbar {
                background-color: var(--white);
                padding: 1rem 2rem;
                position: sticky;
                top: 0;
                z-index: 1000;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }
            
            .navbar-logo {
                display: flex;
                align-items: center;
            }
            
            .navbar-logo img {
                height: 40px;
                width: auto;
            }
            
            .navbar-menu {
                display: flex;
                gap: 2rem;
                align-items: center;
            }
            
            .navbar-menu a {
                text-decoration: none;
                color: var(--dark-text);
                font-weight: 500;
                transition: color 0.3s ease;
            }
            
            .navbar-menu a:hover {
                color: var(--primary-purple);
            }
            
            .navbar-actions {
                display: flex;
                gap: 1rem;
                align-items: center;
            }
            
            .btn {
                padding: 0.5rem 1.25rem;
                border-radius: 6px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s ease;
                display: inline-block;
            }
            
            .btn-login {
                color: var(--dark-text);
            }
            
            .btn-login:hover {
                color: var(--primary-purple);
            }
            
            .btn-primary {
                background: linear-gradient(to right, var(--primary-purple), var(--primary-purple-dark));
                color: var(--white);
                border: none;
            }
            
            .btn-primary:hover {
                opacity: 0.9;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(126, 87, 255, 0.2);
            }
            
            .btn-outline {
                border: 1px solid var(--primary-purple);
                color: var(--primary-purple);
                background: transparent;
            }
            
            .btn-outline:hover {
                background-color: rgba(126, 87, 255, 0.05);
            }
            
            .navbar-toggle {
                display: none;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--dark-text);
            }
            
            /* Hero section */
            .hero-section {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 4rem 2rem;
                max-width: 1200px;
                margin: 0 auto;
                gap: 2rem;
            }
            
            .hero-content {
                flex: 1;
                max-width: 600px;
            }
            
            .hero-title {
                font-size: 3.5rem;
                font-weight: 700;
                margin-bottom: 1.5rem;
                line-height: 1.2;
            }
            
            .hero-title .accent-time {
                color: var(--accent-blue);
            }
            
            .hero-title .accent-priorities {
                color: var(--primary-purple);
            }
            
            .hero-description {
                font-size: 1.1rem;
                color: var(--light-text);
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            
            .hero-cta {
                display: flex;
                gap: 1rem;
                margin-bottom: 2rem;
            }
            
            .hero-features {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .feature-item {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .feature-icon {
                color: #43c6b9;
                font-size: 1.2rem;
            }
            
            /* Calendar widget */
            .calendar-widget {
                flex: 1;
                max-width: 500px;
                background-color: var(--white);
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                padding: 1.5rem;
                position: relative;
                transform: translateY(-20px);
            }
            
            .calendar-widget::before {
                content: '';
                position: absolute;
                top: -10px;
                left: -10px;
                right: -10px;
                bottom: -10px;
                background: linear-gradient(135deg, rgba(126, 87, 255, 0.05), rgba(74, 137, 220, 0.05));
                border-radius: 20px;
                z-index: -1;
            }
            
            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
            }
            
            .calendar-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--dark-text);
            }
            
            .grade-button {
                background-color: var(--primary-purple);
                color: var(--white);
                border-radius: 6px;
                padding: 0.25rem 0.5rem;
                font-weight: 600;
            }
            
            .calendar-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 0.5rem;
                margin-bottom: 1rem;
                text-align: center;
            }
            
            .day-header {
                font-weight: 500;
                color: var(--light-text);
                padding: 0.5rem 0;
            }
            
            .calendar-events {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .calendar-event {
                padding: 0.75rem;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .event-icon {
                font-size: 1.2rem;
            }
            
            .event-details {
                flex: 1;
            }
            
            .event-title {
                font-weight: 600;
                margin-bottom: 0.25rem;
            }
            
            .event-time {
                font-size: 0.9rem;
                color: var(--light-text);
            }
            
            .event-purple {
                background-color: var(--event-purple);
            }
            
            .event-purple .event-icon {
                color: var(--primary-purple);
            }
            
            .event-green {
                background-color: var(--event-green);
            }
            
            .event-green .event-icon {
                color: #43c6b9;
            }
            
            .event-peach {
                background-color: var(--event-peach);
            }
            
            .event-peach .event-icon {
                color: #ff9f43;
            }
            
            .event-mint {
                background-color: var(--event-mint);
            }
            
            .event-mint .event-icon {
                color: #43c6b9;
            }
            
            .calendar-recommendations {
                margin-top: 1.5rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            .recommendations-title {
                font-size: 0.9rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: var(--light-text);
            }
            
            .recommendations-text {
                font-size: 0.9rem;
                color: var(--light-text);
                line-height: 1.5;
            }
            
            /* Calendar icon with face */
            .calendar-icon {
                color: var(--primary-purple);
                font-size: 1.8rem;
                position: relative;
                margin-right: 0.5rem;
            }
            
            .calendar-face {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -40%);
                font-size: 0.9rem;
                color: var(--white);
            }
            
            /* Responsive styles */
            @media (max-width: 992px) {
                .hero-section {
                    flex-direction: column;
                    padding: 3rem 1.5rem;
                }
                
                .hero-content, .calendar-widget {
                    max-width: 100%;
                }
                
                .hero-title {
                    font-size: 2.5rem;
                }
                
                .calendar-widget {
                    transform: translateY(0);
                    margin-top: 2rem;
                }
            }
            
            @media (max-width: 768px) {
                .navbar {
                    padding: 1rem;
                }
                
                .navbar-menu {
                    position: fixed;
                    top: 70px;
                    left: 0;
                    right: 0;
                    background-color: var(--white);
                    flex-direction: column;
                    padding: 1rem;
                    gap: 1rem;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    transform: translateY(-150%);
                    transition: transform 0.3s ease;
                    z-index: 999;
                }
                
                .navbar-menu.active {
                    transform: translateY(0);
                }
                
                .navbar-toggle {
                    display: block;
                }
                
                .navbar-actions {
                    margin-left: auto;
                }
                
                .hero-title {
                    font-size: 2rem;
                }
                
                .hero-cta {
                    flex-direction: column;
                }
            }
        </style>
    </head>
    <body>
        <nav class="navbar">
            <div class="navbar-logo">
                <div class="calendar-icon">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="calendar-face">
                        <i class="far fa-smile"></i>
                    </span>
                </div>
                <span class="logo-text">Own My Calendar</span>
            </div>
            
            <button class="navbar-toggle" id="navbar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="navbar-menu" id="navbar-menu">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#pricing">Pricing</a>
            </div>
            
            <div class="navbar-actions">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/home') }}" class="btn btn-login">Home</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-login">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

        <div id="app">
            <section class="hero-section">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Take Control of <br>
                        Your <span class="accent-time">Time</span> and <br>
                        <span class="accent-priorities">Priorities</span>
                    </h1>
                    
                    <p class="hero-description">
                        Own My Calendar grades your calendar weekly, helping you align your time with your true priorities and maximize productivity.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="{{ route('register') }}" class="btn btn-primary">Try For Free</a>
                        <a href="#how-it-works" class="btn btn-outline">Learn More</a>
                    </div>
                    
                    <div class="hero-features">
                        <div class="feature-item">
                            <span class="feature-icon">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <span>3 Free Calendar Grades</span>
                        </div>
                        
                        <div class="feature-item">
                            <span class="feature-icon">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <span>No Credit Card Required</span>
                        </div>
                    </div>
                </div>
                
                <div class="calendar-widget">
                    <div class="calendar-header">
                        <h2 class="calendar-title">This Week's Calendar</h2>
                        <span class="grade-button">B+</span>
                    </div>
                    
                    <div class="calendar-days">
                        <div class="day-header">M</div>
                        <div class="day-header">T</div>
                        <div class="day-header">W</div>
                        <div class="day-header">T</div>
                        <div class="day-header">F</div>
                        <div class="day-header">S</div>
                        <div class="day-header">S</div>
                    </div>
                    
                    <div class="calendar-events">
                        <div class="calendar-event event-purple">
                            <span class="event-icon">
                                <i class="far fa-calendar"></i>
                            </span>
                            <div class="event-details">
                                <div class="event-title">Team Standup</div>
                                <div class="event-time">9:00 AM</div>
                            </div>
                        </div>
                        
                        <div class="calendar-event event-green">
                            <span class="event-icon">
                                <i class="far fa-calendar"></i>
                            </span>
                            <div class="event-details">
                                <div class="event-title">Client Presentation</div>
                                <div class="event-time">11:00 AM</div>
                            </div>
                        </div>
                        
                        <div class="calendar-event event-peach">
                            <span class="event-icon">
                                <i class="far fa-calendar"></i>
                            </span>
                            <div class="event-details">
                                <div class="event-title">Lunch Break</div>
                                <div class="event-time">1:00 PM</div>
                            </div>
                        </div>
                        
                        <div class="calendar-event event-mint">
                            <span class="event-icon">
                                <i class="far fa-calendar"></i>
                            </span>
                            <div class="event-details">
                                <div class="event-title">Focus Work</div>
                                <div class="event-time">3:00 PM</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="calendar-recommendations">
                        <div class="recommendations-title">Recommendations:</div>
                        <p class="recommendations-text">
                            Add more focus time and reduce meeting blocks to improve your grade.
                        </p>
                    </div>
                </div>
            </section>
        </div>
        
        <script>
            // Toggle mobile menu
            document.addEventListener('DOMContentLoaded', function() {
                const navbarToggle = document.getElementById('navbar-toggle');
                const navbarMenu = document.getElementById('navbar-menu');
                
                if (navbarToggle && navbarMenu) {
                    navbarToggle.addEventListener('click', function() {
                        navbarMenu.classList.toggle('active');
                    });
                }
            });
        </script>
    </body>
</html>
