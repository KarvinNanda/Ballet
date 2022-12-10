@extends('Master.master')

@section('title','Update Transaction')

@section('content')
    <div class="pagetitle">
        <h1>Update Transaction</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('update',$transaction)}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-10">
                            {{$transaction->LongName}}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Jatuh Tempo</label>
                        <div class="col-sm-10">
                            {{\Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y')}}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">Harga</label>
                        <div class="col-sm-10">
{{--                            <input type="number" class="form-control" name="inputHarga" value="{{$transaction->ClassPrice}}">--}}
                            {{$transaction->ClassPrice}}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">Diskon</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="inputDisc" value="{{$transaction->discount}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Keterangan</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputDesc" value="{{$transaction->desc}}">
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-primary p-2 ps-5 pe-5 mb-3">
                            Submit
                        </button>
                    </div>

                    @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{$error}}
                            </div>
                        @endforeach
                    @endif

                </form><!-- End General Form Elements -->

            </div>
        </div>
    </section>


@endsection
