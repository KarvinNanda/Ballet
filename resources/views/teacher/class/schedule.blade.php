@extends('layouts.app')

@section('title', 'Schedules')

@section('content')
    <x-page-header title="Schedules" />

    @include('teacher.class.partials.class-list', ['classes' => $classes])
@endsection
