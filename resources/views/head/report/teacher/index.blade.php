@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Report Teacher Attendance')

@section('content')
    <div class="pagetitle">
        <h1>Report Teacher Attendance</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Month</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                            <tr>
                                <td>{{$item->month}}</td>
                                <td>
                                    <form action="{{route('headTeacherReport',$item->month_num)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">Report</button>
                                    </form>
                                </td>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
    </section>

    <script>

    </script>
@endsection
