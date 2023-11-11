@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Report Active Student')

@section('content')

    <div class="pagetitle">
        <h1>Report Active Student</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="POST"
                    action="{{route('headPrintActiveStudent')}}"
                >

                    @csrf
                    <input class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Course" name="class">
                    <datalist id="datalistOptions">
                        @foreach($classes as $c)
                            <option value="{{$c->class_name}}">{{$c->class_name}}</option>
                        @endforeach
                    </datalist>

                    <button type="submit" class="btn btn-primary text-nowrap">Report</button>
                </form>
            </div>
            <div class="card-body"></div>
        </div>
    </section>

    <script>

    </script>
@endsection
