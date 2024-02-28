@extends('Master.master')

@section('title','Class List')

@section('content')


    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            {{-- <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="GET"
                    action="{{route('headViewaddStudentClass',$class_id)}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">


                    <button type="submit" class="btn btn-primary text-nowrap">Search</button>
                </form>
            </div> --}}

            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Teacher</th>
                            <th scope="col">Class</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $d)
                            <tr>
                                @if(in_array($d->id,$schedules))
                                <td>{{$d->user}}</td>
                                <td>{{$d->class_name}}</td>
                                <td>{{$d->students}}</td>
                                <td>
                                    <form action="{{route('headStudentClassInsertPage',["classId" => $d->id,"id" => $studentId])}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Add Class</button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                {{-- <div class="alert text-center" role="alert">
                    {{$students->links()}}
                </div> --}}
            </div>
        </div>
    </section>


@endsection
