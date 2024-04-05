@extends('Master.master')

@section('title','Class Freeze List')

@section('content')
    <div class="d-none">
        {{ $keyword = request('keyword') }}
        {{ $status = request('status', 'all') }}
    </div>

    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="GET"
                    action="{{route('adminClassFreezeView')}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">

                    @csrf
                    {{-- <select class="form-select" name="status">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                        <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Active</option>
                        <option value="non-aktif" {{ $status == 'non-aktif' ? 'selected' : '' }}>Non Active</option>
                    </select> --}}

                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filters</button>
                </form>
                    {{-- <a href="{{route('adminClassAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Class</button></a> --}}
            </div>
            <div class="card-body">



                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Class Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Detail</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classes as $class)
                        <tr>
                            <td>
                                {{$class->Type->class_name}} -
                                {{$class->mapping[0]->getUser->name}}
                                - {{$class->people_count}}
                            </td>
                            <td>Rp.{{number_format($class->price)}}</td>
                            @if($class->Status == 'aktif')
                            <td>
{{--                                <form action="{{route('adminDetailClass')}}" method="get">--}}
{{--                                    @csrf--}}
{{--                                    <input type="hidden" value="{{$class->id}}" name="classId">--}}
                                    <a href="{{route('adminDetailClassFreeze', ['id' => $class->id])}}"><button type="submit" class="btn btn-secondary">Detail</button></a>
{{--                                </form>--}}
                            </td>
                            @else
                            <td>None</td>
                            <td>None</td>
                            <td>None</td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$classes->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
