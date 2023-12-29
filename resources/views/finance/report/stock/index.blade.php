@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title', 'Report Class Attendence')

@section('content')
    <div class="pagetitle">
        <h1>Report Stock</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="d-flex align-items-center justify-content-center gap-2" method="POST"
                    action="{{ route('financeStockPrintReport') }}">

                    @csrf

                    <label for="start_date">Start</label>
                    <input class="form-control" type="date" id="start_date" name="start_date"
                        value="{{ now()->SetTimeZone('GMT+7')->toDateString() }}">


                    <label for="end_date">End</label>
                    <input class="form-control" type="date" id="end_date" name="end_date"
                        value="{{ now()->SetTimeZone('GMT+7')->toDateString() }}">

                    <button type="submit" class="btn btn-primary text-nowrap me-3">Report</button>


                </form>
            </div>
            <div class="card-body"></div>
        </div>
    </section>

    <script></script>
@endsection
