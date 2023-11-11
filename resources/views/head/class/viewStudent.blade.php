@extends('Master.master')

@section('title','Student List')

@section('content')

    <div class="pagetitle">
        <h1>Student Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">DOB</th>
                            <th scope="col">Address</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Email</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{$student->LongName}}</td>
                                <td>{{\Carbon\Carbon::parse($student->Dob)->format('d M Y')}}</td>
                                <td>{{$student->Address}}</td>
                                <td>{{$student->Phone1}}</td>
                                <td>{{$student->Email}}</td>
                                <td>
                                    <form action="{{route('headAddStudentClass')}}" method="post">
                                        @csrf
                                        <input type="hidden" value="{{$student->id}}" name="studentId">
                                        <input type="hidden" value="{{$class_id}}" name="classId">
                                        <button type="submit" class="btn btn-success">Add Student</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
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
