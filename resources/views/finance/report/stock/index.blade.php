@extends('layouts.app')

@section('title', 'Stock report')

@section('content')
    @php($today = now()->setTimezone('GMT+7')->toDateString())

    <x-page-header title="Stock report" />

    <form class="card" method="POST" action="{{ route('financeStockPrintReport') }}" target="_blank">
        @csrf
        <div class="card-body">
            <x-form.section title="Period">
                <x-form.field name="start_date" label="Start date" type="date" :value="$today" required />
                <x-form.field name="end_date" label="End date" type="date" :value="$today" required help="Opens the PDF in a new tab." />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Open report (PDF)</button>
            </div>
        </div>
    </form>
@endsection
