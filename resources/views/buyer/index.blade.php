@extends('Master.master')

@section('title','Stock')

@section('content')

    <div class="pagetitle">
        <h1>Item Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="GET" action="{{route('buyer')}}">
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
                            <th scope="col"><a href="{{route("buyerSorting",['value' => "name",'type' => $sort])}}">Name</a></th>
                            <th scope="col"><a href="{{route("buyerSorting",['value' => "size",'type' => $sort])}}">Size</a></th>
                            <th scope="col"><a href="{{route("buyerSorting",['value' => "quantity",'type' => $sort])}}">Quantity</a></th>
                            <th scope="col">Action</th>
{{--                            <th scope="col">Delete</th>--}}
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{$stock->name}}</td>
                                <td>{{$stock->size}}</td>
                                <td>{{$stock->quantity}}</td>
                                <td>
                                    <form action="{{route('buyingItem',$stock->id)}}" method="get">
                                        <button type="submit" class="btn btn-info">Buy</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$stocks->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
