@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Report Class Attendence')

@section('content')
    <div class="pagetitle">
        <h1>Report Class Attendence</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Class Name</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                            <tr>
                                <td>{{$item->class_name}}</td>
                                <td>{{$carbon::parse($item->date)->format('d M Y')}}</td>
                                <td>
                                    <form action="{{route('adminClassPrintReport',['header' => $item->header_id,'className' => $item->class_name])}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Report</button>
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
