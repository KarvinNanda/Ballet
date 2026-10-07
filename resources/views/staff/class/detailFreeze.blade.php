@extends('Master.master')

@section('title','Detail Class Freeze')

@section('content')

    <div class="pagetitle">
        <h1>Detail Class</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
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
                        </tr>
                    @empty
                        <tr><td colspan="5">No Data</td></tr>
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
            <div class="card-body">
                @include('staff.class.partials.students', ['actions' => false])
            </div>
        </div>
    </section>

@endsection
