@extends('layouts.app')

@section('title', 'My classes')

@section('content')
    <x-page-header title="My classes" />

    @include('teacher.class.partials.class-list', ['classes' => $classes])
@endsection
