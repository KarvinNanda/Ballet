@extends('layouts.app')

@section('title', 'Add class')

@section('content')
    <x-page-header title="Add class">
        <x-slot:actions>
            <a href="{{ staff_route('class.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to classes</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('class.store') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Class">
                <x-form.field name="inputType" label="Course" type="select" required
                              :options="$types->mapWithKeys(fn ($t) => [$t->id => $t->class_name.' – Rp'.number_format($t->class_price)])->all()" />
                <x-form.field name="inputTeacher" label="Teacher" type="select" required
                              :options="$users->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create class</button>
            </div>
        </div>
    </form>
@endsection
