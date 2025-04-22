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

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ secure_url('password/email') }}" class="login-form">
            @csrf

            <div class="form-group">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email Address" required autocomplete="email" autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn-login">
                    Send Password Reset Link
                </button>
            </div>

            <div class="auth-links">
                @if (Route::has('login'))
                    <div class="register-link">
                        <span>Remember your password?</span>
                        <a href="{{ route('login') }}">
                            Back to login
                        </a>
                    </div>
                @endif
            </div>
        </form>

        <div class="tagline">More money, more time</div>
    </div>
</div>
@endsection
