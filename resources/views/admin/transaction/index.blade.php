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
                            <th scope="col">No</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Jatuh Tempo</th>
                            <th scope="col">Tanggal Bayar</th>
                            <th scope="col"><a href="{{route("adminTransactionSorting","price")}}">Harga</a></th>
                            <th scope="col">Diskon</th>
                            <th scope="col">Total</th>
                            <th scope="col">Keterangan</th>
                            <th scope="col"><a href="{{route("adminTransactionSorting","payment_status")}}">Status</a></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->id}}</td>
                                <td>{{$transaction->LongName}}</td>
                                <td>{{\Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>

                                @if($transaction->transaction_payment == null)
                                    <td>Waiting for Payment</td>
                                @else
                                <td>{{\Carbon\Carbon::parse($transaction->transaction_payment)->format('d M Y')}}</td>
                                @endif

                                <td>Rp.{{number_format($transaction->price)}}</td>
                                <td>{{$transaction->discount}}%</td>
                                @if($transaction->discount != 0)
                                    <td>Rp.{{number_format($transaction->price - (($transaction->discount/100) * $transaction->price) )}}</td>
                                @else
                                    <td>Rp.{{number_format($transaction->price)}}</td>
                                @endif

                                @if($transaction->description == null)
                                    <td>-</td>
                                @else
                                    <td>{{$transaction->description}}</td>
                                @endif



                                @if($transaction->payment_status == "lunas")
                                <td class="text-success font-weight-bold">{{$transaction->payment_status}}</td>
                                @else
                                    <td class="text-danger font-weight-bold">{{$transaction->payment_status}}</td>
                                @endif
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
