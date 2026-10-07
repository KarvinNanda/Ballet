@inject('carbon','Carbon\Carbon')
@extends('Master.master')
@section('title','Transaction')

@section('content')
<div class="d-none">
    {{ $keyword = request('search') }}
</div>

    <div class="pagetitle">
        <h1>Transaction Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="GET" action="{{staff_route('transaction.index')}}">
                    <input class="form-control" type="text" name="search" placeholder="Search" value="{{$keyword}}" title="Enter search keyword">
                </form>
                <a href="{{staff_route("transaction.create")}}"><button class="btn btn-success me-3 mb-3 me-5"> Add Transaction</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Due Date</th>
                            <th scope="col"><a href="{{staff_route('transaction.sort',['column' => 'price','direction' => $sort])}}">Price</a></th>
                            <th scope="col">Discount</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Date</th>
                            <th scope="col"><a href="{{staff_route('transaction.sort',['column' => 'payment_status','direction' => $sort])}}">Status</a></th>
                            <th colspan="2" class="text-center" scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->LongName}}</td>
                                <td>{{$carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>
                                <td>Rp.{{number_format($transaction->price)}}</td>
                                <td>{{str_contains($transaction->discount, '%') ? $transaction->discount : 'Rp '.number_format($transaction->discount) }}</td>
                                @if(str_contains($transaction->discount, '%'))
                                @php
                                    $disc = str_replace("%","",$transaction->discount);
                                @endphp
                                    <td>
                                        Rp.{{number_format($transaction->price - (($disc/100)*$transaction->price))}}
                                    </td>
                                @else
                                    <td>Rp.{{number_format($transaction->price - $transaction->discount)}}</td>
                                @endif
                                <td>{{is_null($transaction->transaction_payment) ? 'Waiting for Payment' : $carbon::parse($transaction->transaction_payment)->format('d M Y')}}</td>
                                <td>{{$transaction->payment_status}}</td>
                                <td class="d-flex">
                                    @can('transaction.edit-paid', $transaction)
                                    <form action="{{staff_route('transaction.edit',$transaction->id)}}" method="get">
                                        <button type="submit" class="btn btn-warning me-2">Update</button>
                                    </form>
                                    @endcan

                                    <form action="{{staff_route('transaction.show',$transaction->id)}}" method="get">
                                        <button type="submit" class="btn btn-secondary me-2">Detail</button>
                                    </form>

                                    @can('transaction.delete')
                                    <form action="{{staff_route('transaction.destroy',$transaction->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger ">Delete</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$transactions->appends(['search' => $keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
