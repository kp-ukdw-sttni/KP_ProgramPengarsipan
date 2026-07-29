<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - E-Archive STTNI Yogyakarta</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="glow-bg"></div>
    <div class="glow-bg-right"></div>
    
    <div class="auth-wrapper">
        @yield('content')
    </div>
</body>
</html>
