@extends('Master.master')

@section('title','Class List')

@section('content')

    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <form action="{{route('headGetAbsen',$view)}}" method="post">
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
                        <th scope="col">Notes</th>

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
                                    <input type="checkbox" name="check[{{$loop->iteration -1}}]" class="form-check-input" id="test" value="on" checked>
                                    @elseif(@$detail[$loop->iteration-1]->Description == "Masuk")
                                        <input type="hidden" name="check[{{$loop->iteration -1}}]" value="off">
                                        <input type="checkbox" name="check[{{$loop->iteration -1}}]" class="form-check-input" id="test" value="on" checked>
                                    @else
                                        <input type="hidden" name="check[{{$loop->iteration -1}}]" value="off">
                                        <input type="checkbox" name="check[{{$loop->iteration -1}}]" class="form-check-input" id="test" value="on">
                                    @endif
                                </td>
                                <td >
                                    <select value="" name="keterangan[]" class="form-select">
                                        <option selected value="{{$detail[$loop->iteration-1]->Description}}">{{@$detail[$loop->iteration-1]->Description ? $detail[$loop->iteration-1]->Description : 'Select...'}}</option>
                                        @if($detail[$loop->iteration-1]->Description == 'Absen')
                                            <option value="Ijin">Ijin</option>
                                            <option value="Sakit">Sakit</option>
                                        @elseif($detail[$loop->iteration-1]->Description == 'Ijin')
                                            <option value="Absen">Absen</option>
                                            <option value="Sakit">Sakit</option>
                                        @elseif($detail[$loop->iteration-1]->Description == 'Sakit')
                                            <option value="Absen">Absen</option>
                                            <option value="Ijin">Ijin</option>
                                        @endif
                                    </select>
                                </td>
                                <td >
                                    @if(!@$detail)
                                        <input type="text" name="notes[]" class="form-control">
                                    @else
                                        <input type="text" name="notes[]" class="form-control" value="{{@$detail[$loop->iteration-1]->Notes}}">
                                     @endif
                                </td>
                            </tr>


                        @endforeach

                    </tbody>
                </table>
            </div>
                    <div class=" mt-3 mb-3 w-100 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success me-5 mt-2 mb-2">Submit</button>
                    </div>
            </form>
        </div>
    </section>


@endsection
