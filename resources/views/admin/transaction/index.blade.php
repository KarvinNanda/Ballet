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
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="GET" action="{{route('adminTransactionPage',['sort' => $sort])}}">
                    <input class="form-control" type="text" name="search" placeholder="Search" value="{{$keyword}}" title="Enter search keyword">
                </form>
                <a href="{{route("addTransaction")}}"><button class="btn btn-success me-3 mb-3 me-5"> Add Transaction</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Due Date</th>
                            <th scope="col"><a href="{{route('adminTransactionSorting',['value' => 'price','type' => $sort])}}">Price</a></th>
                            <th scope="col">Discount</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Date</th>
                            <th scope="col"><a href="{{route('adminTransactionSorting',['value' => 'payment_status','type' => $sort])}}">Status</a></th>
                            <th colspan="2" class="text-center" scope="col">Detail</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->LongName}}</td>
                                <td>{{\Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>
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

                                @if($transaction->transaction_payment == null)
                                    <td>Waiting for Payment</td>
                                @else
                                <td>{{\Carbon\Carbon::parse($transaction->transaction_payment)->format('d M Y')}}</td>
                                @endif

                                <td>{{$transaction->payment_status}}</td>
                                <td class="d-flex justify-content-between">

                                    @if($transaction->transaction_payment == null)
                                    <form action="{{route('adminUpdateTransaction',$transaction->id)}}" method="get">
                                        <button type="submit" class="btn btn-warning me-2">Update</button>
                                    </form>
                                    @else
                                    None
                                    @endif
                                    
                                    <form action="{{route('adminDetailTransaction',$transaction->id)}}" method="get">
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
                    {{$transactions->appends(['search' => $keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
