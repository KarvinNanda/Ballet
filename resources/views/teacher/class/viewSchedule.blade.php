@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Teacher List')

@section('content')

    <div class="pagetitle">
        <h1>Schedule Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            {{-- <div class="mt-3 w-100 d-flex justify-content-end">

                <form action="{{route('viewaddScheduleClass', ['id' => $classId])}}" method="get">
                    @csrf
                    <input type="hidden" value="{{$classId}}" name="classId">
                    <button class="btn btn-success me-5 mt-2 mb-2">Add Schedule</button>
                </form>

                <form action="{{route('viewaddMultipleScheduleClass', ['id' => $classId])}})}}" method="get">
                    @csrf
                    <input type="hidden" value="{{$classId}}" name="classId">
                    <button class="btn btn-success me-5 mt-2 mb-2">Add Multiple Schedule</button>
                </form>
            </div> --}}
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            {{-- <th scope="col">Nama Kelas</th> --}}
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Update</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($class->isEmpty())
                            <td colspan="4">No Data</td>
                        @else
                            @foreach($class as $c)
                            <tr>
                                {{-- <td>{{$c->classname}}</td> --}}
                                <td>{{$carbon::parse($c->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($c->date)->format('H:i:s')}}</td>
                                <td>
                                    <form action="{{route('viewUpdateScheduleClassTeacher')}}" method="get">
                                        <input type="hidden" value="{{$c->id}}" name="scheduleId">
                                        <button class="btn btn-warning me-5 mt-2 mb-2"> Update Schedule</button>
                                    </form>
                                </td>
                                <td>
                                    <td>
                                        @if( ($carbon::parse($c->date)->addDays(1)->toDateString() == $carbon::now()->setTimezone('GMT+7')->toDateString() || $carbon::now()->setTimezone('GMT+7')->toDateString() == $carbon::parse($c->date)->toDateString()) && $carbon::now()->setTimezone('GMT+7')->format('Y-m-d H:i:s') >= $carbon::parse($c->date)->format('Y-m-d H:i:s'))
                                            <form action="{{route('viewAbsen',$c->id)}}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-info">Attendance</button>
                                            </form>
                                        @else
                                            <p class="text-danger">None</p>
                                        @endif
                                    </td>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
{{--                <div class="alert text-center" role="alert">--}}
{{--                    {{$class->links()}}--}}
{{--                </div>--}}
            </div>
        </div>
    </section>


@endsection
