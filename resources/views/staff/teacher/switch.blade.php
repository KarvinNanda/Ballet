@extends('Master.master')

@section('title','Teacher Switch Form')

@section('content')
    <div class="pagetitle">
        <h1>Teacher Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="get" action="{{staff_route('teacher.switch',$teacher)}}">
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
            </div>
            <div class="card-body">
                <div class="d-block text-danger">
                    <h4 class="pe-5">{{$teacher->name}} has an active class</h4>
                    <h5 class="pe-5">Please select teachers below to replace</h5>
                </div>

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
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($teachers as $t)
                            <tr>
                                <td>{{$t->name}}</td>
                                <td>{{$t->percent}}%</td>
                                <td>{{\Carbon\Carbon::parse($t->dob)->format('d M Y')}}</td>
                                <td>{{$t->address}}</td>
                                <td>{{$t->phone}}</td>
                                <td>{{$t->email}}</td>
                                <td >
                                    <form action="{{staff_route('teacher.replace',['teacher' =>$teacher,'replacement' => $t->id])}}" method="post">
                                    @csrf
                                        <button type="submit" class="btn btn-danger">Select</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{ $teachers->links() }}
                    
                </div>
            </div>
        </div>
    </section>


@endsection