<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Own My Calendar') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="//fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/bootstrap-custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modern-login.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auth-custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/logo.css') }}" rel="stylesheet">
    <link href="{{ asset('css/logo-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/input-text.css') }}" rel="stylesheet">
    <link href="{{ asset('css/verify-email.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
