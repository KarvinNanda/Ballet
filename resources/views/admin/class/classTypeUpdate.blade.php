@extends('Master.master')

@section('title','Update Type')

@section('content')
    <div class="pagetitle">
        <h1>Class Type Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('adminChangeTypeClass')}}" method="post">
                    @csrf
                    <input type="hidden" name="typeID" value="{{$type->id}}">
                    <input type="hidden" name="return_url" value="{{$return_url}}">
                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Class Type Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{$type->class_name}}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Class Type Price</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPrice" value="{{$type->class_price}}">
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
