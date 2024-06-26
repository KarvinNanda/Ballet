@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Dashboard')

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
                <form class="search-form d-flex align-items-center" method="GET" action="{{route('finance')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col"><a href="{{route("financeStockViewSorting",['value' => "name",'sort' => $sort])}}">Name</a></th>
                            <th scope="col"><a href="{{route("financeStockViewSorting",['value' => "size",'sort' => $sort])}}">Size</a></th>
                            <th scope="col"><a href="{{route("financeStockViewSorting",['value' => "quantity",'sort' => $sort])}}">Quantity</a></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{$stock->name}}</td>
                                <td>{{$stock->size}}</td>
                                <td>{{$stock->quantity}}</td>
                                <td>
                                    <form action="{{route('in',$stock)}}" method="get">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">Stock</button>
                                    </form>
                                </td>
{{--                                <td>--}}
{{--                                    <form action="{{route('out',$stock)}}" method="post">--}}
{{--                                        @csrf--}}
{{--                                        <button type="submit" class="btn btn-danger">OUT</button>--}}
{{--                                    </form>--}}
{{--                                </td>--}}


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
