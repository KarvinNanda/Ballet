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
            <form action="{{route('getAbsen',$view)}}" method="post">
                @csrf

            <div class="card-body">



                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Nis</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Action</th>
                        <th scope="col">Keterangan</th>


                    </tr>
                    </thead>
                    <tbody>
                        @foreach($class as $c )

                            <tr >
                                <input type="hidden" value="{{$c->nis}}" name="nis[]">
                                <td>{{$c->nis}}</td>
                                <td>{{$c->nama}}</td>
                                <td>
                                    <input type="hidden" name="check[{{$loop->iteration -1}}]" value="off">
                                    <input type="checkbox" name="check[{{$loop->iteration -1}}]" id="test" value="on">
                                </td>
                                <td >
                                    <select value="" name="keterangan[]">
                                        <option selected>Select...</option>
                                        <option value="Absen">Absen</option>
                                        <option value="Ijin">Ijin</option>
                                        <option value="Sakit">Sakit</option>
                                    </select>

                                </td>

                            </tr>


                        @endforeach

                    </tbody>
                </table>
            </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </form>
        </div>
    </section>


@endsection
