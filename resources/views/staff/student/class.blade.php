@extends('Master.master')

@section('title','Class List')

@section('content')


    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Teacher</th>
                            <th scope="col">Class</th>
                            <th scope="col">Students</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $d)
                            @if(in_array($d->id,$schedules))
                            <tr>
                                <td>{{$d->user}}</td>
                                <td>{{$d->class_name}}</td>
                                <td>{{$d->students}}</td>
                                <td>
                                    <form action="{{staff_route('student.class.store',['class' => $d->id,'student' => $student->id])}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Add Class</button>
                                    </form>
                                </td>
                            </tr>
                            @endif
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
