@extends('layouts.guest')

@section('title', 'Link Tidak Berlaku')

@section('content')
    <h1 class="auth-title">Link tidak berlaku</h1>
    <p class="auth-subtitle">Link reset sudah kedaluwarsa atau sudah dipakai. Minta link baru untuk melanjutkan.</p>
    <a href="{{ route('email-page') }}" class="btn btn-primary w-100 py-2">Minta link baru</a>
@endsection
