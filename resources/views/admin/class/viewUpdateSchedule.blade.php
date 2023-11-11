@extends('Master.master')

@section('title','Add Class')

@section('content')
    <div class="pagetitle">
        <h1>Schedule Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>
                <!-- General Form Elements -->
                {{-- <form action="{{route('updateScheduleClass', ['classId' => $class_id])}}" method="post"> --}}
                <form action="{{route('updateScheduleClass', ['id' => $class_id])}}" method="post">
                    @csrf
                    <input type="hidden" value="{{$schedule->id}}" name="scheduleId">
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Date&Time</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" name="dateTime" value="{{$schedule->date}}">
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-primary p-2 ps-5 pe-5 mb-3" type="submit">
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
