@inject('carbon','Carbon\Carbon')
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
                            <th scope="col">Nama</th>
                            <th scope="col">Tanggal Lahir</th>
                            <th scope="col">Umur</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $item)
                            <tr>
                                <td>{{$item->student_name}}</td>
                                <td>{{$carbon::parse($item->student_dob)->format('d M Y')}}</td>
                                <td>{{$item->student_old}} Tahun</td>
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
