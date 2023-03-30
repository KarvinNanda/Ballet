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

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">NIS</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->nis}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Long Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->LongName}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Nick Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->ShortName}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Age</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->age}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Email}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputDOB" class="col-sm-2 col-form-label">DOB</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->dob}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputAddress" class="col-sm-2 col-form-label">Address</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Address}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Parent Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->nama_orang_tua}}</p>
                    </div>
                </div>

                @if(@$detail->bank)
                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Bank Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->bank}}</p>
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Sender Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->pengirim}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Rekening</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->rek}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">City</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->City}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Postal Code</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->kode_pos}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">First Phone</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Phone1}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Second Phone</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Phone2}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Whatsapp</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Whatsapp}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Instagram</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Instagram}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Line</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->Line}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Enroll Date</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->EnrollDate}}</p>
                    </div>
                </div>

            </div>
        </div>
    </section>


@endsection
