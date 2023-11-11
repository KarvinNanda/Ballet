@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Update Stock')

@section('content')
    <div class="pagetitle">
        <h1>Stock Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('StockUpdate',$stock)}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputName" value="{{$stock->name}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Size</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputSize" value="{{$stock->size}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputDOB" class="col-sm-2 col-form-label">Quantity</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="inputQty" value="{{$stock->quantity}}">
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-warning p-2 ps-5 pe-5 mb-3">
                            Submit
                        </button>
                    </div>

                    @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{$error}}
                            </div>
                        @endforeach
                    @endif

                </form><!-- End General Form Elements -->

                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Buyer</th>
                            <th scope="col">Total</th>
                            <th scope="col">Buy Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($buyer)
                            @foreach($buyer as $b)
                                <tr>
                                    <td>{{$b->name}}</td>
                                    <td>{{$b->qty}}</td>
                                    <td>{{$carbon::parse($b->created_at)->format('Y-m-d')}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3"></td>
                            </tr>
                        @endif
                        </tbody>
                    </div>
                </table>

                @if($buyer)
                    <div class="alert text-center" role="alert">
                        {{$buyer->links()}}
                    </div>
                @endif

            </div>
        </div>
    </section>


@endsection
