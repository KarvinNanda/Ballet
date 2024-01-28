@inject('carbon','Carbon\Carbon')
@extends('Master.master')
@section('title','Transaction')

@section('content')

    <div class="pagetitle">
        <h1>Transaction Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchTransaction')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route("headAddTransactionPage")}}"><button class="btn btn-success me-3 mb-3 me-5"> Add Transaction</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Due Date</th>
                            <th scope="col"><a href="{{route('headTransactionSorting',['column' => 'price','type' => $sort])}}">Price</a></th>
                            <th scope="col">Discount</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Date</th>
                            <th scope="col"><a href="{{route('headTransactionSorting',['column' => 'payment_status','type' => $sort])}}">Status</a></th>
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
                                <td>{{is_null($transaction->transaction_payment) ? 'Waiting for Payment' : $transaction->transaction_payment}}</td>
                                <td>{{$transaction->payment_status}}</td>
                                <td class="d-flex">
                                    <form action="{{route('updateTransaction',$transaction->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-warning me-2">Update</button>
                                    </form>

                                    <form action="{{route('detailTransaction',$transaction->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary">Detail</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$transactions->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
