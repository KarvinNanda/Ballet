@php
    $user = auth()->user();
    $navigation = \App\Support\NavigationMenu::fromConfig($user->role ?? null)->build(request()->path(), (int) ($user->id ?? 0));
    $hasSidebar = $navigation !== [] && ! request()->is('buyer*');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · En Pointe</title>
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    {{-- Loaded in <head> because existing pages run inline jQuery inside their content. --}}
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    @stack('head')
</head>
<body class="{{ $hasSidebar ? '' : 'no-sidebar' }}">
<a href="#main" class="visually-hidden-focusable skip-link">Lewati ke konten</a>

@include('partials.header', ['user' => $user, 'hasSidebar' => $hasSidebar])

@if ($hasSidebar)
    @include('partials.sidebar', ['navigation' => $navigation])
@endif

<main id="main" class="app-main">
    @yield('content')
</main>

@if ($hasSidebar)
    @include('partials.bottom-nav', ['navigation' => $navigation])
@endif

@include('partials.toast')

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
