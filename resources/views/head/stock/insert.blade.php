@extends('Master.master')

@section('title','Add Stock')

@section('content')
    <div class="pagetitle">
        <h1>Add Stock</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('StockAdd')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputName">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Size</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputSize">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">Quantity</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="inputQty">
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
