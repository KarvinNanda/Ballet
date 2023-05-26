@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','View Class')

@section('content')
    <div class="pagetitle">
        <h1>Today Schedule</h1>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Class Name</th>
                        <th scope="col">Count Student</th>
                        <th scope="col">View Student</th>
                        <th scope="col">Schedule Detail</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                            <tr>
                                <td>{{$item[0]->class}}</td>
                                @foreach($item as $i)
                                    <td>{{$i->students}}</td>
                                @endforeach
                                <td>
                                    <form action="{{route('viewDetailTeacher',$item[0]->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-info">View</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('viewScheduleClassTeacher')}}" method="post">
                                    @csrf
                                    <input type="hidden" value={{$item[0]->id}} name="classId">
                                    <button type="submit" class="btn btn-secondary">Schedule</button>
                                    </form>
                                </td>
                            </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
    </section>


@endsection
