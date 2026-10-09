@extends('layouts.app')

@section('title', 'Add rule')

@section('content')
    <x-page-header title="Add rule" subtitle="Shown in the terms & conditions dialog of Add student.">
        <x-slot:actions>
            <a href="{{ route('Rules') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to rules</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ route('RulesAdd') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Rule">
                <x-form.field name="inputLanguage" label="Language" required help="The title of this rule, e.g. Indonesia or English." />
                <div class="form-field form-field-wide">
                    <label for="content" class="form-label">Content<span class="required-mark" aria-hidden="true">*</span></label>
                    {{-- No "required" attribute: CKEditor hides this textarea and a hidden required field blocks the submit silently. RuleRequest still requires it. --}}
                    <textarea id="content" name="content" class="form-control{{ $errors->has('content') ? ' is-invalid' : '' }}" rows="10" @error('content') aria-describedby="content-error" aria-invalid="true" @enderror>{{ old('content') }}</textarea>
                    @error('content')
                        <div id="content-error" class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create rule</button>
            </div>
        </div>
    </form>

    @include('head.rule._editor')
@endsection
