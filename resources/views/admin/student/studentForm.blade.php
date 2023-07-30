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
                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">NIS</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputNis">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Long Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLongName">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Nick Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputNickName">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputEmail">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">DOB</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="inputDate_of_Birth">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Address</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" style="height: 100px" name="inputAddress"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Parent Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputParentName">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label ">Account Number</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputRekening">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">City</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputCity">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Postal Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPostalCode">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">First Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone1">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Second Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone2">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Whatsapp</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputWhatsapp">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Instagram</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputInstagram">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Line</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLine">
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-success p-2 ps-5 pe-5 mb-3">
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
