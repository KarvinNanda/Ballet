@extends('layouts.guest')

@section('title', 'Password Baru')

@section('content')
    <h1 class="auth-title">Password baru</h1>
    <p class="auth-subtitle">Minimal 8 karakter.</p>

    <form method="POST" action="{{ route('reset-password', $token) }}" novalidate>
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        @error('email')
            <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label for="password" class="form-label">Password baru</label>
            <input id="password" name="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="new-password" minlength="8" required autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Ulangi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="form-control" autocomplete="new-password" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Simpan password</button>
    </form>
@endsection
