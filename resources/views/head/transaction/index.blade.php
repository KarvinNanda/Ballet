@extends('Master.master')
@section('title','Transaction')

@section('content')

    <div class="pagetitle">
        <h1>Transaction Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchTransaction')}}">
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
                            <th scope="col">Nama</th>
                            <th scope="col">Jatuh Tempo</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Diskon</th>
                            <th scope="col">Total</th>
                            <th scope="col">Keterangan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Update</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->LongName}}</td>
                                <td>{{\Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>
                                <td>Rp.{{number_format($transaction->ClassPrice)}}</td>
                                <td>{{$transaction->discount}}%</td>
                                @if($transaction->discount != 0)
                                    <td>Rp.{{number_format($transaction->ClassPrice - (($transaction->discount/100)*$transaction->ClassPrice))}}</td>
                                @else
                                    <td>Rp.{{number_format($transaction->ClassPrice)}}</td>
                                @endif
                                <td>{{$transaction->desc}}</td>
                                <td>{{$transaction->payment_status}}</td>
                                <td>
                                    <form action="{{route('updateTransaction',$transaction)}}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">Update</button>
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
