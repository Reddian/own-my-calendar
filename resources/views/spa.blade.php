<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config("app.name", "Own My Calendar") }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="//fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome -->
    {{-- <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> --}}

    <!-- Bootstrap CSS -->
    {{-- <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <!-- Custom CSS are now imported via app.js and handled by Vite -->
    {{-- <link href="{{ asset("css/fonts.css") }}" rel="stylesheet"> --}}
    {{-- <link href="{{ asset("css/dashboard.css") }}" rel="stylesheet"> --}}
    {{-- <link href="{{ asset("css/bootstrap-custom.css") }}" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body>
    <div id="app">
        <!-- Vue application will mount here -->
    </div>

    <!-- Bootstrap JS Bundle (Consider if needed globally or handled by Vue components) -->
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

