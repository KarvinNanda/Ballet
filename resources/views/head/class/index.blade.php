@extends('Master.master')

@section('title','Class List')

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
                    action="{{route('headClassPage')}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">
                    
                    <select class="form-select" name="status">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                        <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Active</option>
                        <option value="non-aktif" {{ $status == 'non-aktif' ? 'selected' : '' }}>Non Active</option>
                    </select>

                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filters</button>
                </form>

                <a href="{{route('headClassAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Class</button></a>
            </div>
            <div class="card-body">



                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">
                            <a href="{{route("sorting",['value' =>"class_name",'type' => $sort])}}">
                                Class Name
                            </a>
                        </th>
                        <th scope="col">Price</th>
                        <th scope="col">
                            <a href="{{route("sorting",['value' =>"status",'type' => $sort])}}">
                                Action
                            </a>
                        </th>
                        <th scope="col">Detail</th>
                        <th scope="col">Schedule Detail</th>
                        <th scope="col">Level Up</th>
                        {{-- <th scope="col">Reset</th> --}}
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
                            <td>Rp.{{number_format($class->class_transaction_price)}}</td>
                            <td>
                                <form action="{{route('changeStatusClass',$class)}}" method="post">
                                    @csrf
                                    @if($class->Status == 'aktif')
                                        <button type="submit" class="btn btn-primary">Active</button>
                                    @else
                                        <button type="submit" class="btn btn-primary">Inactive</button>
                                    @endif
                                </form>
                            </td>
                            @if($class->Status == 'aktif')
                                <td>
                                    <form action="{{route('headDetailClass', ['id' => $class->id])}}" method="get">
                                        @csrf
                                        <input type="hidden" value="{{$class->id}}" name="classId">
                                        <button type="submit" class="btn btn-secondary">Detail</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('headViewScheduleClass', ['classId' => $class->id])}}" method="get">
                                        @csrf
                                        <input type="hidden" value="{{$class->id}}" name="classId">
                                        <button type="submit" class="btn btn-info">Schedule</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('headLevelUp')}}" method="post">
                                        @csrf
                                        <input type="hidden" value="{{$class->id}}" name="classId">
                                        <button type="submit" class="btn btn-info">Level Up</button>
                                    </form>
                                </td>
                                @else
                                <td>None</td>
                                <td>None</td>
                                <td>None</td>
                                @endif
                                <td>
                                    <form action="{{route('headDeleteClass',$class->id)}}"  method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$classes->appends(['keyword' => $keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
