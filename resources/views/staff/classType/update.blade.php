@extends('layouts.app')

@section('title', 'Update course · '.$type->class_name)

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    <x-page-header title="Update course" :subtitle="$type->class_name">
        <x-slot:actions>
            <a href="{{ staff_route('class-type.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to courses</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('class-type.update') }}">
        @csrf
        <input type="hidden" name="typeID" value="{{ $type->id }}">
        <input type="hidden" name="return_url" value="{{ $back }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Course">
                {{-- The name cannot be changed here: shown as text, not posted. --}}
                <div class="form-field">
                    <span class="form-label d-block">Course</span>
                    <p class="form-static">{{ $type->class_name }}</p>
                </div>
                <x-form.field name="inputPrice" label="Price (Rp)" type="number" min="0" :value="$type->class_price" required
                              help="The new price applies to Unpaid transactions and to classes that are not frozen." />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection
