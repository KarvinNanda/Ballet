@inject('carbon','Carbon\Carbon')
@extends('Master.master')
@section('title','Transaction')

@section('content')

    <div class="pagetitle">
        <h1>Transaction Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100">
                <form class="search-form d-flex align-items-center" method="get" action="{{route('searchTransaction')}}">
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
                            <th scope="col">Name</th>
                            <th scope="col">Due Date</th>
                            <th scope="col"><a href="{{route('financeTransactionSorting','price')}}">Price</a></th>
                            <th scope="col">Discount</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Date</th>
                            <th scope="col"><a href="{{route('financeTransactionSorting','payment_status')}}">Status</a></th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->LongName}}</td>
                                <td>{{$carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>
                                <td>Rp.{{number_format($transaction->price)}}</td>
                                <td>Rp.{{number_format($transaction->discount)}}</td>
                                @if($transaction->discount != 0)
                                    <td>Rp.{{number_format($transaction->price - (($transaction->discount/100)*$transaction->price))}}</td>
                                @else
                                    <td>Rp.{{number_format($transaction->price)}}</td>
                                @endif
                                <td>{{is_null($transaction->transaction_payment) ? 'Waiting for Payment' : $transaction->transaction_payment}}</td>
                                <td>{{$transaction->payment_status}}</td>
                                <td>
                                    @if($transaction->payment_status == 'Unpaid')
                                    <form action="{{route('paidTransaction',$transaction->id)}}" method="get">
                                        <button type="submit" class="btn btn-warning me-2">Update</button>
                                    </form>
                                    @else
                                        None
                                    @endif
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
