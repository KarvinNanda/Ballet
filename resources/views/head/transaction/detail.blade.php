@inject('carbon','Carbon\Carbon')
@extends('Master.master')

@section('title','Detail Student')

@section('content')
    <div class="pagetitle">
        <h1>Student Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Nama</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->LongName}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Jatuh Tempo</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->transaction_date}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Kelas</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->ClassName}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Harga</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">Rp.{{number_format($detail->ClassPrice)}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Diskon</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->discount == 0 ? 0 :$detail->discount}}%</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputAddress" class="col-sm-2 col-form-label">Keterangan</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->desc == null ? '' : $detail->desc}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Total</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">Rp.{{number_format($detail->price - (($detail->discount/100)*$detail->price))}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputDOB" class="col-sm-2 col-form-label">Tanggal Bayar</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{now()->toDateString()}}</p>
                    </div>
                </div>



                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Status</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->payment_status}}</p>
                    </div>
                </div>


            </div>
        </div>
    </section>


@endsection
