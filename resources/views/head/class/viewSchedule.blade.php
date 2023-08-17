@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Schedule List')

@section('content')

    <div class="pagetitle">
        <h1>Schedule Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="mt-3 w-100 d-flex justify-content-end">
                <form action="{{route('headViewaddScheduleClass', ['id' => $classId])}}" method="get">
                    <button class="btn btn-success me-5 mt-2 mb-2">Add Schedule</button>
                </form>

                <form action="{{route('headViewaddMultipleScheduleClass', ['id' => $classId])}}" method="get">
                    <button class="btn btn-success me-5 mt-2 mb-2">Add Multiple Schedule</button>
                </form>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Update</th>
                            <th scope="col">Delete</th>
                            <th scope="col">Attendence</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($class->isEmpty())
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>None</td>
                            <td>None</td>
                            <td>None</td>
                        @else
                            @foreach($class as $c)
                            <tr>
                                <td>{{$carbon::parse($c->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($c->date)->format('H:i:s')}}</td>
                                <td>
                                    <form action="{{route('headViewUpdateScheduleClass')}}" method="post">
                                        @csrf
                                        <input type="hidden" value="{{$c->id}}" name="scheduleId">
                                        <button class="btn btn-warning"> Update Schedule</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route("headDeleteSchedule",['id'=>$c->id,'classId'=>$classId])}}" method="get">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Delete Schedule</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('headViewAbsen',$c->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-info">Attendence</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </div>
                </table>
                {{$class->links()}}
                <!-- End Table with stripped rows -->
            </div>
        </div>
    </section>


@endsection
