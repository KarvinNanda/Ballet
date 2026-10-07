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
                <a href="{{staff_route('schedule.create', $class->id)}}" class="btn btn-success me-5 mt-2 mb-2">Add Schedule</a>
                <a href="{{staff_route('schedule.multiple.create', $class->id)}}" class="btn btn-success me-5 mt-2 mb-2">Add Multiple Schedule</a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Update</th>
                        <th scope="col">Delete</th>
                        @can('attendance.record')
                            <th scope="col">Attendance</th>
                        @endcan
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($schedules as $s)
                        <tr>
                            <td>{{$carbon::parse($s->date)->format('d M Y')}}</td>
                            <td>{{$carbon::parse($s->date)->format('H:i:s')}}</td>
                            <td>
                                <a href="{{staff_route('schedule.edit', $s->id)}}" class="btn btn-warning">Update Schedule</a>
                            </td>
                            <td>
                                <form action="{{staff_route('schedule.destroy', $s->id)}}" method="post" data-confirm="Hapus data ini?">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Delete Schedule</button>
                                </form>
                            </td>
                            @can('attendance.record')
                                <td>
                                    <a href="{{staff_route('attendance.edit', $s->id)}}" class="btn btn-info">Attendance</a>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No Data</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                {{$schedules->links()}}
                <!-- End Table with stripped rows -->
            </div>
        </div>
    </section>


@endsection
