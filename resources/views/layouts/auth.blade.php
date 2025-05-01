<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config("app.name", "Own My Calendar") }}</title>

    <!-- Fonts (Keep CDN for simplicity) -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="//fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Vite CSS & JS for Auth Pages -->
    {{-- Font Awesome and Bootstrap CSS/JS should be imported within auth.css/auth.js --}}
    @vite(["resources/css/auth.css", "resources/js/auth.js"])

</head>
<body>
    <div id="auth-app"> 
        <main>
            @yield("content")
        </main>
    </div>
</body>
</html>

