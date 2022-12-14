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
                        <th scope="col">Day</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                            <tr>
                                <td>{{$item->class}}</td>
                                <td>{{$item->students}}</td>
                                <td>{{$carbon::parse($item->date)->englishDayOfWeek}}</td>
                                <td>{{$carbon::parse($item->date)->format('d M Y')}}</td>
                                <td>{{$carbon::parse($item->date)->format('H:i:s')}}</td>
                                <td>
                                    <form action="{{route('viewDetail',$item->class)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-success">View</button>
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
