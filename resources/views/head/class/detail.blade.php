@extends('Master.master')

@section('title','Detail Class')

@section('content')

    <div class="pagetitle">
        <h1>Detail Class</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <a href="{{route('headViewaddTeacherClass',$id)}}"><button class="btn btn-info me-5 mt-2 mb-2" type="Submit">Add Teacher</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
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
                        @if($teachers->isEmpty())
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                        @else
                            @foreach($teachers as $teacher)
                                <tr>
                                    <td>{{$teacher->teacherName}}</td>
                                    <td>{{\Carbon\Carbon::parse($teacher->teacherDOB)->format('d M Y')}}</td>
                                    <td>{{$teacher->teacherAddress}}</td>
                                    <td>{{$teacher->teacherPhone}}</td>
                                    <td>{{$teacher->teacherEmail}}</td>
                                        <td>
                                            <form action="{{route('headClassDeleteTeacher',['teacher' => $teacher->id,'class' => $id])}}" method="get">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </td>
                                </tr>
                            @endforeach
                        @endif


                        </tbody>
                    </div>
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
                <a href="{{route('headViewaddStudentClass',$id)}}"><button class="btn btn-info me-5 mt-2 mb-2" type="Submit">Add Student</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">DOB</th>
                            <th scope="col">Address</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($students->isEmpty())
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                        @else
                            @foreach($students as $student)
                                <tr>
                                    <td>{{$student->studentName}}</td>
                                    <td>{{\Carbon\Carbon::parse($student ->studentDOB)->format('d M Y')}}</td>
                                    <td>{{$student->studentAddress}}</td>
                                    <td>{{$student->studentPhone}}</td>
                                    <td>{{$student->studentEmail}}</td>
                                    <td>
                                        <form action="{{route('headClassDeleteStudent',['student' => $student->id,'class' => $id])}}" method="get">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif


                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$students->links()}}
                </div>

            </div>
        </div>
    </section>




@endsection
