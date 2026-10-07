@extends('Master.master')

@section('title','Class Freeze List')

@section('content')
    @php($keyword = request('keyword'))

    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="GET"
                    action="{{staff_route('class.freeze.index')}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">

                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filters</button>
                </form>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Class Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Detail</th>
                        @can('class.freeze-price')
                            <th scope="col">Update</th>
                        @endcan
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
                                <a href="{{staff_route('class.freeze.show', $class)}}"><button type="button" class="btn btn-secondary">Detail</button></a>
                            </td>
                            @can('class.freeze-price')
                                <td>
                                    <a href="{{staff_route('class.freeze.edit', $class)}}"><button type="button" class="btn btn-warning">Update</button></a>
                                </td>
                            @endcan
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
