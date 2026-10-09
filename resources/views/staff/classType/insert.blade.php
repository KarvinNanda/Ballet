@extends('layouts.app')

@section('title', 'Add course')

@section('content')
    <x-page-header title="Add course">
        <x-slot:actions>
            <a href="{{ staff_route('class-type.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to courses</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('class.course.store') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Course">
                <x-form.field name="inputName" label="Course name" required />
                <x-form.field name="inputPrice" label="Price (Rp)" type="number" min="0" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create course</button>
            </div>
        </div>
    </form>
@endsection
