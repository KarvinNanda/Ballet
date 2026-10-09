@props(['account' => null])
{{-- Shared fields of a staff account (teacher, finance, admin). $account is null on Add; the slot adds page-specific fields. --}}
<x-form.section title="Account">
    <x-form.field name="inputName" label="Name" :value="$account?->name" required />
    <x-form.field name="inputEmail" label="Email" type="email" :value="$account?->email" required />
    <x-form.field name="inputDate_of_Birth" label="Date of birth" type="date" :value="$account?->dob" required />
    <x-form.field name="inputPhone" label="Phone" type="tel" :value="$account?->phone" required help="10–12 digits" />
    <x-form.field name="inputAddress" label="Address" type="textarea" :value="$account?->address" required wide />
    {{ $slot }}
</x-form.section>
