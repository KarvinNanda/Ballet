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
                <form class="search-form d-flex align-items-center" method="get" action="{{route('headStockPage')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('headStockAddPage')}}"><button class="btn btn-success me-3 mb-3"> Add Stock</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col"><a href="{{route("headStockViewSorting",['value' => "name",'sort' => $sort])}}">Name</a></th>
                            <th scope="col"><a href="{{route("headStockViewSorting",['value' => "size",'sort' => $sort])}}">Size</a></th>
                            <th scope="col"><a href="{{route("headStockViewSorting",['value' => "quantity",'sort' => $sort])}}">Quantity</a></th>
                            <th scope="col">Update</th>
                            <th scope="col">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{$stock->name}}</td>
                                <td>{{$stock->size}}</td>
                                <td>{{$stock->quantity}}</td>
                                <td>
                                    <form action="{{route('headStockUpdatePage',$stock)}}" method="get">
                                        <button type="submit" class="btn btn-warning">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('stockDelete',$stock)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>


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
