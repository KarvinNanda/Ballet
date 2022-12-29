@extends('Master.master')

@section('title','Add Student')

@section('content')
    <div class="pagetitle">
        <h1>Student Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('adminStudentForm')}}" method="post">
                    @csrf
                    @foreach($details as $detail)
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">NIS</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="inputNis" value="{{$detail->nis}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Long Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLongName" value="{{$detail->LongName}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Nick Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputNickName" value="{{$detail->ShortName}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Age</label>
                        <div class="col-sm-10">
                            <input type="age" class="form-control" name="inputNickName" value="{{$detail->age}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputEmail" value="{{$detail->Email}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">DOB</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="inputDate_of_Birth" value="{{$detail->dob}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputAddress" class="col-sm-2 col-form-label">Address</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" style="height: 100px" name="inputAddress" readonly>{{$detail->Address}}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Parent Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputParentName" value="{{$detail->nama_orang_tua}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Bank Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputParentName" value="{{$detail->bank}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Sender Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputSenderName" value="{{$detail->pengirim}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Rekening</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputRekening" value="{{$detail->rek}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">City</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputCity" value="{{$detail->City}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Postal Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPostalCode" value="{{$detail->kode_pos}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">First Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone1" value="{{$detail->Phone1}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Second Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone2" value="{{$detail->Phone2}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Whatsapp</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputWhatsapp" value="{{$detail->Whatsapp}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Instagram</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputInstagram" value="{{$detail->Instagram}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Instagram</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="inputInstagram" value="{{$detail->EnrollDate}}" readonly>
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-primary p-2 ps-5 pe-5 mb-3">
                            Submit
                        </button>
                    </div>
                    @endforeach
                </form><!-- End General Form Elements -->

            </div>
        </div>
    </section>


@endsection
