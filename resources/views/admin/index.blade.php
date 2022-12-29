@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Dashboard')

@section('content')
    <div class="pagetitle">
        <h1>Today Schedule</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Teacher Name</th>
                        <th scope="col">Class Name</th>
                        <th scope="col">Day</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                        @if($item->date >= $carbon::now()->toDateString() && $item->date <= $carbon::now()->addDays(7)->toDateString())
                            <tr>
                                <td>{{$item->teacherName}}</td>
                                <td>{{$item->class}}</td>
                                <td>{{$carbon::parse($item->date)->englishDayOfWeek}}</td>
                                <td>{{$carbon::parse($item->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($item->date)->format('H:i:s')}}</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
    </section>


@endsection
