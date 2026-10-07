@extends('Master.master')

@section('title','Stock')

@section('content')
<div class="d-none">
    {{ $keyword = request('search') }}
</div>

    <div class="pagetitle">
        <h1>Stock Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="get" action="{{staff_route('stock.index')}}">
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                @can('stock.manage')
                <a href="{{staff_route('stock.create')}}"><button class="btn btn-success me-3 mb-3"> Add Stock</button></a>
                @endcan
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col"><a href="{{staff_route("stock.sort",['column' => "name",'direction' => $sort])}}">Name</a></th>
                            <th scope="col"><a href="{{staff_route("stock.sort",['column' => "size",'direction' => $sort])}}">Size</a></th>
                            <th scope="col"><a href="{{staff_route("stock.sort",['column' => "quantity",'direction' => $sort])}}">Quantity</a></th>
                            @can('stock.manage')
                            <th scope="col">Update</th>
                            <th scope="col">Delete</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{$stock->name}}</td>
                                <td>{{$stock->size}}</td>
                                <td>{{$stock->quantity}}</td>
                                @can('stock.manage')
                                <td>
                                    <form action="{{staff_route('stock.edit',$stock)}}" method="get">
                                        <button type="submit" class="btn btn-warning">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{staff_route('stock.destroy',$stock)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                                @endcan
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$stocks->appends(['search' => $keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
