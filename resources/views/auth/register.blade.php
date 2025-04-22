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

        <form method="POST" action="{{ secure_url('register') }}" class="login-form">
            @csrf

            <div class="form-group">
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Name" required autocomplete="name" autofocus>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email Address" required autocomplete="email">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password" required autocomplete="new-password">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <button type="submit" class="btn-login">
                    Register
                </button>
            </div>

            <div class="auth-links">
                @if (Route::has('login'))
                    <div class="register-link">
                        <span>Already have an account?</span>
                        <a href="{{ route('login') }}">
                            Log in
                        </a>
                    </div>
                @endif
            </div>
        </form>

        <div class="tagline">More money, more time</div>
    </div>
</div>
@endsection
