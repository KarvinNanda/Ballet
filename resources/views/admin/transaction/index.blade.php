@extends('Master.master')
@section('title','Transaction')

@section('content')

    <div class="pagetitle">
        <h1>Transaction Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('adminSearchTransaction')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword" class="ms-3">
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
                                <td>{{$transaction->discount}}%</td>
                                @if($transaction->discount != 0)
                                    <td>Rp.{{number_format($transaction->price - (($transaction->discount/100) * $transaction->price) )}}</td>
                                @else
                                    <td>Rp.{{number_format($transaction->price)}}</td>
                                @endif

                                @if($transaction->transaction_payment == null)
                                    <td>Waiting for Payment</td>
                                @else
                                <td>{{\Carbon\Carbon::parse($transaction->transaction_payment)->format('d M Y')}}</td>
                                @endif

                                @if($transaction->payment_status == "lunas")
                                <td class="text-success font-weight-bold">{{$transaction->payment_status}}</td>
                                @else
                                    <td class="text-danger font-weight-bold">{{$transaction->payment_status}}</td>
                                @endif
                                <td class="d-flex">
                                    <form action="{{route('adminUpdateTransaction',$transaction->id)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-warning me-2">Update</button>
                                    </form>

                                    <form action="{{route('adminDetailTransaction',$transaction->student_id)}}" method="post">
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
