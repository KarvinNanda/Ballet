@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','In-Out Stock')

@section('content')
    <div class="pagetitle">
        <h1>Detail Stock Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('makeReport',[$stock,$type])}}" method="post">
                    @csrf
                    <input type="hidden" name="return_url" value="{{$return_url}}">
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$stock->name}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Size</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$stock->size}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Quantity</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$stock->quantity}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{$type == 'in' ? 'IN' : 'OUT'}} (Optional)</label>
                        <div class="col-sm-10">
                            <input type="number" name="in_out" class="form-control">
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-primary p-2 ps-5 pe-5 mb-3">
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
                            <th scope="col">Served By</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($buyer)
                        @foreach($buyer as $b)
                            <tr>
                                <td>{{$b->name}}</td>
                                <td>{{$b->qty}}</td>
                                <td>{{$carbon::parse($b->created_at)->format('Y-m-d')}}</td>
                                <td>{{$b->served_by}}</td>
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
