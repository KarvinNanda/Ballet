@extends('layouts.app')

@section('title', 'Today')

@section('content')
    <x-page-header title="Today" :subtitle="$date->format('l, d M Y')" />

    <x-filter-bar :action="route('teacher')">
        <label for="teacher-home-search" class="visually-hidden">Search by class or student</label>
        <input id="teacher-home-search" class="form-control" type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Search class or student…">
    </x-filter-bar>

    <section class="card" aria-labelledby="today-title">
        <div class="card-body">
            <h2 id="today-title" class="card-title">Today's sessions</h2>
            @if ($today->isEmpty())
                <x-empty-state icon="calendar3" title="No sessions today" />
            @else
                @include('teacher.partials.sessions', ['sessions' => $today])
            @endif
        </div>
    </section>

    @if ($yesterday->isNotEmpty())
        <section class="card" aria-labelledby="yesterday-title">
            <div class="card-body">
                <h2 id="yesterday-title" class="card-title">Still open from yesterday</h2>
                @include('teacher.partials.sessions', ['sessions' => $yesterday])
            </div>
        </section>
    @endif
@endsection
