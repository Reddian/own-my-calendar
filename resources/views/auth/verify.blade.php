@extends('layouts.auth')

@section('content')
<div class="login-page">
    <!-- Decorative Elements -->
    <div class="coin coin-sm coin-1"></div>
    <div class="coin coin-md coin-2"></div>
    <div class="coin coin-sm coin-3"></div>
    <div class="coin-stack">
        <div class="coin coin-lg"></div>
    </div>
    <!-- Clock with hour markings -->
    <div class="clock">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="gear"></div>

    <div class="login-container">
        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ secure_asset('images/OwnMyCalendarLogoLight.png') }}" alt="Own My Calendar" class="logo-image">
        </div>

        <div class="verify-email-content">
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <p class="verify-text">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
            <p class="verify-text">{{ __('If you did not receive the email') }},</p>
            
            <form class="resend-form" method="POST" action="{{ secure_url('email/resend') }}">
                @csrf
                <button type="submit" class="btn-login">
                    {{ __('Resend Verification Email') }}
                </button>
            </form>

            <div class="auth-links">
                @if (Route::has('login'))
                    <div class="register-link">
                        <a href="{{ route('login') }}">
                            Back to login
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="tagline">More money, more time</div>
    </div>
</div>
@endsection
