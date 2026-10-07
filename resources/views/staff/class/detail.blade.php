@extends('Master.master')

@section('title','Detail Class')

@section('content')

    <div class="pagetitle">
        <h1>Detail Class</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <a href="{{staff_route('class.teacher.create', $class_id)}}"><button class="btn btn-success me-5 mt-2 mb-2" type="button"> Add Teacher</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">DOB</th>
                        <th scope="col">Address</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td>{{$teacher->teacherName}}</td>
                            <td>{{\Carbon\Carbon::parse($teacher->teacherDOB)->format('d M Y')}}</td>
                            <td>{{$teacher->teacherAddress}}</td>
                            <td>{{$teacher->teacherEmail}}</td>
                            <td>{{$teacher->teacherPhone}}</td>
                            <td>
                                <form action="{{staff_route('class.teacher.destroy', ['teacher' => $teacher->id, 'class' => $class_id])}}" method="post" data-confirm="Hapus data ini?">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No Data</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$teachers->links()}}
                </div>

            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <a href="{{staff_route('class.student.create', $class_id)}}"><button class="btn btn-success me-5 mt-2 mb-2" type="button"> Add Student</button></a>
                <form action="{{staff_route('class.reset-quota', $class_id)}}" method="post" class="d-inline" data-confirm="Reset quota semua murid di kelas ini?">
                    @csrf
                    <button class="btn btn-secondary me-5 mt-2 mb-2" type="submit"> Reset Quota</button>
                </form>
            </div>
            <div class="card-body">
                @include('staff.class.partials.students', ['actions' => true])
            </div>
        </div>
    </section>

@endsection
