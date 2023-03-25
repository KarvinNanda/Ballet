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
                            <th scope="col"><a href="{{route('headTransactionSorting','price')}}">Price</a></th>
                            <th scope="col">Discount</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Date</th>
                            <th scope="col"><a href="{{route('headTransactionSorting','payment_status')}}">Status</a></th>
                            <th colspan="2" class="text-center" scope="col">Update</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->LongName}}</td>
                                <td>{{$carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>
                                <td>Rp.{{number_format($transaction->price)}}</td>
                                <td>{{$transaction->discount == 0 ? 0 : $transaction->discount}}%</td>
                                @if($transaction->discount != 0)
                                    <td>Rp.{{number_format($transaction->price - (($transaction->discount/100)*$transaction->price))}}</td>
                                @else
                                    <td>Rp.{{number_format($transaction->price)}}</td>
                                @endif
                                <td>{{$carbon::now()->format('d M Y')}}</td>
                                <td>{{$transaction->payment_status}}</td>
                                <td class="d-flex">
                                    <form action="{{route('updateTransaction',$transaction->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-warning me-2">Update</button>
                                    </form>

                                    <form action="{{route('detailTransaction',$transaction->student_id)}}" method="post">
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
