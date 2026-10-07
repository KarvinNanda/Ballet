@extends('Master.master')

@section('title','Class List')

@section('content')
    @php
        $keyword = request('keyword');
        $status = request('status', 'all');
    @endphp

    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="GET"
                    action="{{staff_route('class.index')}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">

                    <select class="form-select" name="status">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                        <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Active</option>
                        <option value="non-aktif" {{ $status == 'non-aktif' ? 'selected' : '' }}>Non Active</option>
                    </select>

                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filters</button>
                </form>
                <a href="{{staff_route('class.create')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Class</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">
                            <a href="{{staff_route('class.sort', ['column' => 'class_name', 'direction' => $sort])}}">
                                Class Name
                            </a>
                        </th>
                        <th scope="col">Price</th>
                        <th scope="col">
                            <a href="{{staff_route('class.sort', ['column' => 'status', 'direction' => $sort])}}">
                                Status
                            </a>
                        </th>
                        <th scope="col">Detail</th>
                        <th scope="col">Schedule Detail</th>
                        <th scope="col">Freeze</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classes as $class)
                        <tr>
                            <td>
                                {{$class->Type?->class_name}} -
                                {{$class->mapping->first()?->getUser?->name ?? '-'}}
                                - {{$class->people_count}}
                            </td>
                            <td>Rp.{{number_format($class->class_transaction_price)}}</td>
                            <td>
                                <form action="{{staff_route('class.status', $class)}}" method="post">
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
                                <a href="{{staff_route('class.show', $class)}}"><button type="button" class="btn btn-secondary">Detail</button></a>
                            </td>
                            <td>
                                <a href="{{staff_route('schedule.index', $class->id)}}"><button type="button" class="btn btn-info">Schedule</button></a>
                            </td>
                            <td>
                                <form action="{{staff_route('class.level')}}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{$class->id}}" name="classId">
                                    <button type="submit" class="btn btn-info">Freeze</button>
                                </form>
                            </td>
                            @else
                            <td>None</td>
                            <td>None</td>
                            <td>None</td>
                            @endif
                            <td>
                                <form action="{{staff_route('class.destroy', $class)}}" method="post" data-confirm="Hapus kelas ini?">
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
                    {{$classes->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
