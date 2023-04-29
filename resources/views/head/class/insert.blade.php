@extends('Master.master')

@section('title','Add Class')

@section('content')
    <div class="pagetitle">
        <h1>Class Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('ClassAdd')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputType" class="col-sm-2 col-form-label">Course</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="inputType">
                                <option selected></option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->class_name }} - {{$type->class_price}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputTeacher" class="col-sm-2 col-form-label">Teacher</label>
                        <div class="col-sm-10">
                            <select class="form-select" name="inputTeacher">
                                <option selected></option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
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
