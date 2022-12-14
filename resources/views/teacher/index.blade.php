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
                        <th scope="col">Class Name</th>
                        <th scope="col">Day</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                        @if($carbon::parse($item->date)->toDateString() >= $carbon::now()->toDateString() && $carbon::parse($item->date)->toDateString() <= $carbon::now()->addDays(7)->toDateString())
                            <tr>
                                <td>{{$item->class}}</td>
                                <td>{{$carbon::parse($item->date)->englishDayOfWeek}}</td>
                                <td>{{$carbon::parse($item->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($item->date)->format('H:i:s')}}</td>
                                <td>
                                    @if($carbon::parse($item->date)->toDateTimeLocalString() <= $carbon::now()->setTimezone('GMT+7')->toDateTimeLocalString() && $carbon::parse($item->date)->addHours(2)->toDateTimeLocalString() >= $carbon::now()->setTimezone('GMT+7')->toDateTimeLocalString())

                                        <form action="{{route('viewAbsen',$item->id)}}" method="post">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Absent</button>
                                        </form>
                                    @else
                                        <p class="text-danger">None</p>
                                    @endif
                                </td>
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
