@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Teacher List')

@section('content')

    <div class="pagetitle">
        <h1>Schedule Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="mt-3 w-100 d-flex justify-content-end">
                <form action="{{route('adminViewAddScheduleClass', ['id' => $class_id])}}" method="get">
                    @csrf
                    <input type="hidden" value="{{$class_id}}" name="classId">
                    <button class="btn btn-success me-5 mt-2 mb-2"> Add Schedule</button>
                </form>

                <form action="{{route('adminViewAddMultipleScheduleClass')}}" method="get">
                    @csrf
                    <input type="hidden" value="{{$class_id}}" name="classId">
                    <button class="btn btn-success me-5 mt-2 mb-2"> Add Multiple Schedule</button>
                </form>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            {{-- <th scope="col">Nama Kelas</th> --}}
                            <th scope="col">Tanggal</th>
                            <th scope="col">Waktu</th>
                            <th scope="col">Update</th>
                            <th scope="col">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($class->isEmpty())
                            {{-- <td>No Data</td> --}}
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                        @else
                            @foreach($class as $c)
                            <tr>
                                {{-- <td>{{$c->classname}}</td> --}}
                                <td>{{$carbon::parse($c->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($c->date)->format('H:i:s')}}</td>
                                <td>
                                    <form action="{{route('viewUpdateScheduleClass', ['id' => $class_id])}}" method="get">
                                        @csrf
                                        <input type="hidden" value="{{$c->id}}" name="scheduleId">
                                        <button class="btn btn-success me-5 mt-2 mb-2">Update Schedule</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route("deleteSchedule",['id'=>$c->id,'classId'=>$class_id])}}" method="get">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Delete Schedule</button>
                                    </form>
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
