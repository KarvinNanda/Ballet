@extends('Master.master')

@section('title','Multiple Schedule')

@section('content')
    <div class="pagetitle">
        <h1>Multiple Schedule</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{staff_route('schedule.multiple.store')}}" method="post">
                    @csrf
                    @if($class)
                        <input type="hidden" value="{{$class->id}}" name="classId">
                    @else
                        <div class="row mb-3">
                            <label for="classId" class="col-sm-2 col-form-label">Class</label>
                            <div class="col-sm-10">
                                <select class="form-select" id="classId" name="classId">
                                    @foreach($classes as $c)
                                        <option value="{{$c->id}}" @selected(old('classId') == $c->id)>{{$c->class_name}} (#{{$c->id}})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-3">
                        <label for="dateTime" class="col-sm-2 col-form-label">Date&Time</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="dateTime" name="dateTime" value="{{old('dateTime')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="ScheduleLoop" class="col-sm-2 col-form-label">Schedule Loop</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="ScheduleLoop" name="ScheduleLoop" placeholder="Schedule Loop" min="1" max="52" value="{{old('ScheduleLoop')}}">
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
