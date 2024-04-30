@extends('Master.master')

@section('title','Teacher List')

@section('content')
<div class="d-none">
    {{ $keyword = request('search') }}
</div>

    <div class="pagetitle">
        <h1>Teacher Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchTeacher')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('headTeacherAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Teacher</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Percentage</th>
                            <th scope="col">DOB</th>
                            <th scope="col">Address</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Email</th>
                            <th scope="col" colspan="2">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($teachers as $teacher)
                            <tr>
                                <td>{{$teacher->name}}</td>
                                <td>{{$teacher->percent}}%</td>
                                <td>{{\Carbon\Carbon::parse($teacher->dob)->format('d M Y')}}</td>
                                <td>{{$teacher->address}}</td>
                                <td>{{$teacher->phone}}</td>
                                <td>{{$teacher->email}}</td>
                                <td class="d-flex">
                                    <form action="{{route('TeacherDelete',$teacher)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger me-3">Delete</button>
                                    </form>
                                    <form action="{{route('TeacherUpdatePage',$teacher)}}" method="get">
                                        <button type="submit" class="btn btn-warning">Update</button>
                                    </form>
                                </td>


                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$teachers->appends(['search'=>$keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
