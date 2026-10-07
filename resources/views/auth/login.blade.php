@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h1 class="auth-title">Selamat datang</h1>
    <p class="auth-subtitle">Masuk ke akun En Pointe kamu.</p>

    @include('partials.auth-alerts')

    <form method="POST" action="{{ route('do-login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   autocomplete="username" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="current-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <a href="{{ route('email-page') }}" class="small fw-semibold">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
    </form>
@endsection
