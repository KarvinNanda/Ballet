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
                    Pilih
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{route('adminStudentActive')}}">Aktif</a></li>
                    <li><a class="dropdown-item" href="{{route('adminStudentNonActive')}}">Non-Aktif</a></li>
                </ul>
            </div>
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('adminStudentSearch')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('adminStudentForm')}}"><button class="btn btn-success me-3 mb-3"> Add Student</button></a>
            </div>

            <div class="card-body">


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col"><a href="{{route("adminStudentViewSorting","dob")}}">Tanggal Lahir</a></th>
                            <th scope="col"><a href="{{route("adminStudentViewSorting","age")}}">Umur</a></th>
                            <th scope="col">Orang Tua</th>
                            <th scope="col">Rekening</th>
                            <th scope="col">Pengirim</th>
                            <th scope="col">Bank</th>
                            {{--                        <th scope="col">Alamat</th>--}}
                            <th scope="col">Phone</th>
                            <th scope="col">Action</th>
                            <th scope="col">Action</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{$student->name}}</td>
                                <td>{{\Carbon\Carbon::parse($student->dob)->format('d M Y')}}</td>
                                <td>{{$student->age}}</td>
                                <td>{{$student->ortu}}</td>
                                <td>{{$student->rek}}</td>
                                <td>{{$student->pengirim}}</td>
                                <td>{{$student->bank}}</td>
                                <td>{{$student->phone}}</td>
                                <td>
                                    <form action="{{route('adminStudentChange',$student->id)}}" method="post">
                                        @csrf
                                        @if($student->status == 'aktif')
                                            <button type="submit" class="btn btn-danger">Deactive</button>
                                        @else
                                            <button type="submit" class="btn btn-danger">Active</button>
                                        @endif
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('adminStudentDelete',$student->id)}}" method="get">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('adminStudentDetail',$student->id)}}" method="get">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Detail</button>
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
