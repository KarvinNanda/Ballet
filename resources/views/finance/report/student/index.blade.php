@extends('layouts.app')

@section('title', 'Active student finance report')

@section('content')
    <x-page-header title="Active student finance report" />

    <form class="card" method="POST" action="{{ route('financeStudentReport') }}" target="_blank">
        @csrf
        <div class="card-body">
            <x-form.section title="Filter">
                <x-form.field name="status" label="Payment status" type="select" :options="['' => 'All statuses', 'Paid' => 'Paid', 'Unpaid' => 'Unpaid']" />
                <x-form.field name="class" label="Course" type="select" :options="['' => 'All courses'] + $classes->pluck('class_name', 'class_name')->all()" help="Opens the PDF in a new tab." />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Open report (PDF)</button>
            </div>
        </div>
    </form>
@endsection
