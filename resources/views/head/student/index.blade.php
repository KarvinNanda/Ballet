@inject('carbon','Carbon\Carbon')
@extends('Master.master')

@section('title','Student List')

@section('content')

    <div class="pagetitle">
        <h1>Student Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="dropdown mt-3 ms-3">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Choose
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{route('activeStudentPage')}}">Active</a></li>
                    <li><a class="dropdown-item" href="{{route('nonactiveStudentPage')}}">Non-Active</a></li>
                </ul>
            </div>
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchStudent')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('headStudentAddPage')}}"><button class="btn btn-success me-3 mb-3">Add Student</button></a>
            </div>

            <div class="card-body">


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                    <thead>
                    <tr>
                        <th scope="col"><a href="{{route("sortingStudent","name")}}">Name</a></th>
                        <th scope="col"><a href="{{route("sortingStudent","dob")}}">Date of Birth</a> </th>
                        <th scope="col"><a href="{{route("sortingStudent","age")}}">Age</a> </th>
                        <th scope="col">Parent</th>
                        <th scope="col">Bank Account</th>
                        <th scope="col">Sender</th>
                        <th scope="col">Phone</th>
                        <th colspan="2" class="text-center" scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{$student->name}}</td>
                            <td>{{$carbon::parse($student->dob)->format('d M')}}</td>
                            <td>{{$student->age}}</td>
                            <td>{{$student->ortu}}</td>
                            <td>{{$student->rek}}</td>
                            <td>{{$student->pengirim}}</td>
                            <td>{{$student->phone}}</td>
                            <td>
                                <form action="{{route('nonactiveStudent',$student->id)}}" method="post">
                                    @csrf
                                    @if($student->status == 'aktif')
                                        <button type="submit" class="btn btn-primary">Deactive</button>
                                    @else
                                        <button type="submit" class="btn btn-primary">Active</button>
                                    @endif
                                </form>
                            </td>
                            <td>
                                <form action="{{route('detailStudent',$student->name)}}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">Detail</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
            </div>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
    </section>


@endsection
