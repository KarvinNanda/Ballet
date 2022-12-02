@extends('Master.master')

@section('title','AdminPage')

@section('content')
    <div class="pagetitle">
        <h1>Teacher Tables</h1>
    </div><!-- End Page Title -->
    <section class="section">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Teacher</h5>
                    <a href="{{route('adminTeacherForm')}}"><button class="btn btn-success me-5"> Add Teacher</button></a>
                </div>


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Teacher Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Address</th>
                        <th scope="col">DOB</th>
                        <th scope="col">Salary</th>
                        <th scope="col">Update</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>

                        @if($teachers->isEmpty())
                            <th scope="row">No Data</th>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>

                        @else
                            @foreach($teachers as $key => $teacher)
                                <tr>
                                    <th scope="row">{{++$key}}</th>
                                    <td>{{$teacher->name}}</td>
                                    <td>{{$teacher->Email}}</td>
                                    <td>{{$teacher->Phone}}</td>
                                    <td>{{$teacher->Address}}</td>
                                    <td>{{$teacher->Dob}}</td>
                                    <td>Rp. {{$teacher->Salary}}</td>

                                    <td>
                                        <a href=""><button class="btn btn-warning text-white">Update</button></a>
                                    </td>
                                    <td>
                                        <a href="{{route('adminTeacherDelete',$teacher->TeacherId)}}">
                                            <button class="btn btn-danger">Delete</button>
                                        </a>

                                    </td>
                                </tr>
                            @endforeach
                        @endif


                    </tbody>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
    </section>
@endsection
