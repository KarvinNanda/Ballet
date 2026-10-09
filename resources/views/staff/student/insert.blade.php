@extends('layouts.app')

@section('title', 'Add student')

@section('content')
    <x-page-header title="Add student">
        <x-slot:actions>
            <a href="{{ staff_route('student.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to students</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('student.store') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />

            <x-form.section title="Identity">
                <x-form.field name="inputNis" label="NIS" />
                <x-form.field name="inputLongName" label="Long name" required />
                <x-form.field name="inputNickName" label="Nick name" required />
                <x-form.field name="inputDate_of_Birth" label="Date of birth" type="date" required />
            </x-form.section>

            <x-form.section title="Contact">
                <x-form.field name="inputParentName" label="Parent name" required />
                <x-form.field name="inputEmail" label="Email" type="email" required />
                <x-form.field name="inputPhone1" label="First phone" type="tel" required help="10–12 digits" />
                <x-form.field name="inputPhone2" label="Second phone" type="tel" />
                <x-form.field name="inputWhatsapp" label="WhatsApp" type="tel" required help="10–12 digits" />
                <x-form.field name="inputInstagram" label="Instagram" help="Without @" />
                <x-form.field name="inputLine" label="Line" />
                <x-form.field name="inputCity" label="City" required />
                <x-form.field name="inputPostalCode" label="Postal code" required />
                <x-form.field name="inputAddress" label="Address" type="textarea" required wide />
            </x-form.section>

            <x-form.section title="Payment">
                <x-form.field name="inputBankName" label="Bank name" />
                <x-form.field name="inputNamaPengirim" label="Sender name" />
                <x-form.field name="inputRekening" label="Account number" />
            </x-form.section>

            <x-form.section title="Terms & conditions">
                <div class="form-field form-field-wide">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#termsModal">
                        <i class="bi bi-file-text" aria-hidden="true"></i> Read terms
                    </button>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="terms_accepted" value="1" id="field-terms_accepted" required @checked(old('terms_accepted')) @error('terms_accepted') aria-describedby="field-terms_accepted-error" @enderror>
                        <label class="form-check-label" for="field-terms_accepted">The parent has read and agrees to the terms and conditions</label>
                        @error('terms_accepted')
                            <div id="field-terms_accepted-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-form.section>

            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create student</button>
            </div>
        </div>
    </form>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="termsModalLabel">Terms & conditions</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion accordion-flush" id="termsAccordion">
                        @foreach ($rules as $rule)
                            {!! $rule->safe_content !!}
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
