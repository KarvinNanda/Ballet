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
                <form class="search-form d-flex align-items-center" method="get" action="{{route('adminStockPage')}}">
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
                            <th scope="col"><a href="{{route("adminStockViewSorting",['value' => "name",'sort' => $sort])}}">Name</a></th>
                            <th scope="col"><a href="{{route("adminStockViewSorting",['value' => "size",'sort' => $sort])}}">Size</a></th>
                            <th scope="col"><a href="{{route("adminStockViewSorting",['value' => "quantity",'sort' => $sort])}}">Quantity</a></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{$stock->name}}</td>
                                <td>{{$stock->size}}</td>
                                <td>{{$stock->quantity}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$stocks->appends(['search' => $keyword])->links()}} <br>
{{--                    <i>Page {{$stocks->currentPage()}}</i> --}}
                </div>
            </div>
        </div>
    </section>


@endsection
