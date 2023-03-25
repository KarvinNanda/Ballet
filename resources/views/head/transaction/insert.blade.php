@extends('Master.master')

@section('title','Add Transaction')

@section('content')
    <div class="pagetitle">
        <h1>Transaction Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('headAddTransaction')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">NIS</label>
                        <div class="col-sm-10">
                            <input class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Nis - Name" name="nis">
                            <datalist id="datalistOptions">
                                @foreach($students as $s)
                                    <option value="{{$s->id}}">{{$s->nis}} - {{$s->LongName}}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Class</label>
                        <div class="col-sm-10">

                            <input class="form-control" list="datalistClass" id="exampleDataList" placeholder="Class" name="class">
                            <datalist id="datalistClass">
                                @foreach($class_transaction as $ct)
                                    <option value="{{$ct->id}}">{{$ct->id}} - {{$ct->ClassName}}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Due Date</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="dateTime">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Price</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="Price" placeholder="Price">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Discount</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="Discount" placeholder="Discount">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Description</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="Description" placeholder="Description">
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
