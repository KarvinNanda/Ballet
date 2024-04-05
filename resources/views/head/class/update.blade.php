@extends('Master.master')

@section('title','Update Class')

@section('content')
    <div class="pagetitle">
        <h1>Class Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('headUpdateClassFreeze',$class_id)}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputType" class="col-sm-2 col-form-label">Course</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$class->class_name}}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputType" class="col-sm-2 col-form-label">Price</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="price" name="inputPrice" value="{{$class->price}}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputTeacher" class="col-sm-2 col-form-label">Teacher</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$class->name}}</p>
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
