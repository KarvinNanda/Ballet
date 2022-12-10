@extends('Master.master')

@section('title','AdminPage')

@section('content')
    <div class="pagetitle">
        <h1>Teacher Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">General Form Elements</h5>

                <!-- General Form Elements -->
                <form action="" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputName">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" name="inputEmail">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">DOB</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="inputDOB">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputAddress" class="col-sm-2 col-form-label">Address</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" style="height: 100px" name="inputAddress"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputSalary" class="col-sm-2 col-form-label">Salary</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="inputSalary">
                        </div>
                    </div>
                    <div class="justify-content-end d-flex">
                        <button class="btn btn-primary p-2 ps-5 pe-5">
                            Submit
                        </button>
                    </div>

                </form><!-- End General Form Elements -->

            </div>
        </div>
    </section>


@endsection
