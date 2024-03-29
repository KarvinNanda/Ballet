@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Dashboard')

@section('content')
    <div class="d-none">
        {{ $keyword = request('keyword') }}
        {{ $status = request('status', 'all') }}
    </div>

    <div class="pagetitle">
        <h1>Today Schedule</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">

            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="GET"
                    action="{{route('teacher')}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">

                    <button type="submit" class="btn btn-primary text-nowrap">Search</button>
                </form>
            </div>

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
{{--                        @if($carbon::parse($item->date)->addDays(1)->toDateString() >= $carbon::now()->setTimezone('GMT+7')->toDateString() && $carbon::parse($item->date)->toDateString() <= $carbon::now()->setTimezone('GMT+7')->addDays(7)->toDateString())--}}
                            <tr>
                                <td>
                                    {{@$item->Type->class_name}} -
                                        {{$item->mapping[0]->getUser->name}}
                                    - {{$item->people_count}}
                                </td>
                                <td>{{$carbon::parse($item->date)->englishDayOfWeek}}</td>
                                <td>{{$carbon::parse($item->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($item->date)->format('H:i:s')}}</td>
                                <td>
                                    @if( ($carbon::parse($item->date)->addDays(1)->toDateString() == $carbon::now()->setTimezone('GMT+7')->toDateString() || $carbon::now()->setTimezone('GMT+7')->toDateString() == $carbon::parse($item->date)->toDateString()) && $carbon::now()->setTimezone('GMT+7')->format('Y-m-d H:i:s') >= $carbon::parse($item->date)->format('Y-m-d H:i:s'))
                                        <form action="{{route('viewAbsen',$item->schedule_id)}}" method="post">
                                            @csrf
                                            <button type="submit" class="btn btn-info">Attendance</button>
                                        </form>
                                    @else
                                        <p class="text-danger">None</p>
                                    @endif
                                </td>
                            </tr>
{{--                        @endif--}}
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$data->links()}}
                </div>

            </div>
        </div>
    </section>


@endsection
