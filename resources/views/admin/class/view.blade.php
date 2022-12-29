@extends('Master.master')

@section('title','Class List')

@section('content')
    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="dropdown mt-3 ms-3">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Pilih
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{route('activeClassPage')}}">Aktif</a></li>
                    <li><a class="dropdown-item" href="{{route('nonactiveClassPage')}}">Non-Aktif</a></li>
                </ul>
            </div>
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchClass')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('headClassAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Class</button></a>
            </div>
            <div class="card-body">



                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Nama Kelas</th>
                        <th scope="col">Harga Kelas</th>
                        <th scope="col">Action</th>
                        <th scope="col">Detail</th>
                        <th scope="col">Schedule Detail</th>
                        <th scope="col">Reset</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classes as $class)
                        <tr>
                            <td>{{$class->ClassName}}</td>
                            <td>Rp.{{number_format($class->ClassPrice)}}</td>
                            <td>
                                <form action="{{route('changeStatusClass',$class)}}" method="post">
                                    @csrf
                                    @if($class->Status == 'aktif')
                                        <button type="submit" class="btn btn-danger">Deactive</button>
                                    @else
                                        <button type="submit" class="btn btn-danger">Active</button>
                                    @endif
                                </form>
                            </td>
                            <td>
                                <form action="{{route('adminDetailClass')}}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{$class->id}}" name="classId">
                                    <button type="submit" class="btn btn-success">Detail</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{route('viewScheduleClass')}}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{$class->id}}" name="classId">
                                    <button type="submit" class="btn btn-success">Schedule</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{route('resetClass',$class->id)}}" onclick="return confirm('Are you sure?')" method="get">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Reset Class</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$classes->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
