@extends('Master.master')

@section('title','Course List')

@section('content')
    <div class="pagetitle">
        <h1>Course Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <div></div>
                <a href="{{route('headCourseAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2">Add Course</button></a>
            </div>
            <div class="card-body">
                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Course</th>
                        <th scope="col">Price</th>
                        <th scope="col">Update</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($types as $type)
                        <tr>
                            <td>
                                {{$type->class_name}}
                            </td>
                            <td>Rp. {{number_format($type->class_price)}}</td>
                            <td>
                                <form action="{{route('headViewChangeTypeClass')}}" method="post">
                                     @csrf
                                     <input type="hidden" name="typeID" value="{{$type->id}}">
                                    <button type="submit" class="btn btn-warning">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{$types->links()}}
            </div>
        </div>
    </section>


@endsection
