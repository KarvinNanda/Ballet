@extends('layouts.app')

@section('title', 'Active students report')

@section('content')
    <x-page-header title="Active students report" />

    <form class="card" method="POST" action="{{ staff_route('report.active-student.print') }}" target="_blank">
        @csrf
        <div class="card-body">
            <x-form.field name="class" label="Course" type="select" :options="['' => 'All courses'] + $classes->pluck('class_name', 'class_name')->all()" help="Opens the PDF in a new tab." />
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Open report (PDF)</button>
            </div>
        </div>
    </form>
@endsection
