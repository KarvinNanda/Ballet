@extends('Master.master')

@section('title','Update Teacher')

@section('content')
    <div class="pagetitle">
        <h1>Teacher Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('adminTeacherUpdatePage',$teacher)}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputName" value="{{$teacher->name}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputEmail" value="{{$teacher->email}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">DOB</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="inputDate_of_Birth" value="{{$teacher->dob}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputAddress" class="col-sm-2 col-form-label">Address</label>
                        <div class="col-sm-10">
                            @if($teacher->address == '' || is_null($teacher->address))
                            <textarea class="form-control" style="height: 100px" name="inputAddress"></textarea>
                            @else
                                <textarea class="form-control" style="height: 100px" name="inputAddress">{{$teacher->address}}</textarea>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone" value="{{$teacher->phone}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Reward(%)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputBonus" value="{{$teacher->percent}}">
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
