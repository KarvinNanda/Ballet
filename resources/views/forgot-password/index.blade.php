@extends('layouts.guest')

@section('title', 'Lupa Password')

@section('content')
    <h1 class="auth-title">Lupa password</h1>
    <p class="auth-subtitle">Masukkan email akun kamu. Kami kirim link untuk membuat password baru.</p>

    <form method="POST" action="{{ route('check-email') }}" novalidate>
        @csrf
        <div class="mb-4">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   autocomplete="email" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Kirim link reset</button>
    </form>

    <p class="text-center mt-4 mb-0 small"><a href="{{ route('login') }}" class="fw-semibold">Kembali ke login</a></p>
@endsection
