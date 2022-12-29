@extends('Master.master')

@section('title','Add Teacher')

@section('content')
    <div class="pagetitle">
        <h1>Teacher Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('adminTeacherForm')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputName" value="{{$detail->name}}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputEmail" value="{{$detail->email}}" readonly>
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
                            <textarea class="form-control" style="height: 100px" name="inputAddress"  readonly>{{$detail->address}}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Phone</label>
                        <div class="col-sm-10" >
                            <input type="text" class="form-control" name="inputPhone" value="{{$detail->phone}}" readonly>
                        </div>
                    </div>

                </form><!-- End General Form Elements -->

            </div>
        </div>
    </section>


@endsection
