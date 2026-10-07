<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · En Pointe</title>
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
</head>
<body>
<main class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <img src="{{ asset('assets/img/logo-hitam.png') }}" alt="" aria-hidden="true">
            <span>En Pointe</span>
        </div>
        @yield('content')
    </div>
</main>
</body>
</html>
