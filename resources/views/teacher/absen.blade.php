@extends('Master.master')

@section('title','Class List')

@section('content')

    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
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
                                    @if(!@$detail)
                                    <input type="hidden" name="check[{{$loop->iteration -1}}]" value="off">
                                    <input type="checkbox" name="check[{{$loop->iteration -1}}]" class="form-check-input" id="test" value="on" >
                                    @elseif(@$detail[$loop->iteration-1]->Description == "Masuk")
                                        <input type="checkbox" name="check[{{$loop->iteration -1}}]" class="form-check-input" id="test" value="on" checked disabled>
                                    @else
                                        <input type="checkbox" name="check[{{$loop->iteration -1}}]" class="form-check-input" id="test" value="on" disabled>
                                    @endif
                                </td>
                                <td >
                                    @if(!@$detail)
                                    <select value="" name="keterangan[]" class="form-select">
                                        <option selected>Select...</option>
                                        <option value="Absen">Absen</option>
                                        <option value="Ijin">Ijin</option>
                                        <option value="Sakit">Sakit</option>
                                    </select>
                                    @else
                                        <select value="" name="keterangan[]" class="form-select" disabled>
                                            <option selected> {{@$detail[$loop->iteration-1]->Description}}</option>
                                        </select>
                                    @endif
                                </td>

                            </tr>


                        @endforeach

                    </tbody>
                </table>
            </div>
                @if(!@$detail)
                    <div class=" mt-3 mb-3 w-100 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success me-5 mt-2 mb-2">Submit</button>
                    </div>
                @endif
            </form>
        </div>
    </section>


@endsection
