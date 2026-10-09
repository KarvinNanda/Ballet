@extends('layouts.app')

@section('title', 'Add transaction')

@section('content')
    <x-page-header title="Add transaction">
        <x-slot:actions>
            <a href="{{ staff_route('transaction.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to transactions</a>
        </x-slot:actions>
    </x-page-header>

    @php
        $studentOptions = $students->mapWithKeys(fn ($s) => [$s->id => trim(($s->nis ? $s->nis.' – ' : '').$s->LongName)])->all();
        $classError = $errors->first('class');
        $oldClass = old('class');
        $oldClass = is_array($oldClass) ? null : $oldClass; // crafted array input; never cast an array
    @endphp

    <form class="card" method="post" action="{{ staff_route('transaction.store') }}" data-transaction-form>
        @csrf
        <div class="card-body">
            <x-form.error-summary />

            <x-form.section title="Billing">
                <x-form.field name="nis" label="Student" type="select" :options="$studentOptions" required />

                {{-- Plain markup: each option carries data-price, which x-form.field options cannot. --}}
                <div class="form-field">
                    <label for="field-class" class="form-label">Class<span class="required-mark" aria-hidden="true">*</span></label>
                    <select id="field-class" name="class" class="form-select{{ $classError ? ' is-invalid' : '' }}" required data-class-select
                            @if ($classError) aria-describedby="field-class-error" aria-invalid="true" @endif>
                        <option value="" @selected(blank($oldClass))>Select…</option>
                        @foreach ($class_transaction as $ct)
                            <option value="{{ $ct->id }}" data-price="{{ $ct->class_price ?? 0 }}" @selected((string) $oldClass === (string) $ct->id)>{{ $ct->class_name }} – {{ $ct->name ?? 'No teacher' }}</option>
                        @endforeach
                    </select>
                    @if ($classError)
                        <div id="field-class-error" class="invalid-feedback d-block">{{ $classError }}</div>
                    @endif
                </div>

                <x-form.field name="dateTime" label="Due date" type="date" required />
                <x-form.field name="Price" label="Price" type="number" min="0" required data-price-input help="Filled from the class; you can change it." />
            </x-form.section>

            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create transaction</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/pages/transaction-form.js') }}" defer></script>
@endpush
