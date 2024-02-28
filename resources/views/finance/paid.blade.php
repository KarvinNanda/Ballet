@extends('Master.master')

@section('title','Payment')

@section('content')
    <div class="pagetitle">
        <h1>Payment</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('doPaidTransaction',$trans)}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$transaction->LongName}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Due Date</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{\Illuminate\Support\Carbon::parse($transaction->transaction_date)->toDateString()}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Class</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$transaction->class_name}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Price</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">Rp.{{number_format($transaction->class_price)}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Discount</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$transaction->discount == 0 ? 0:$transaction->discount}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Description</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$transaction->desc}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Total</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">Rp.{{number_format($transaction->class_price - (($transaction->discount/100)*$transaction->class_price))}}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Quota</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputQuota" value="{{$transaction->transaction_quota}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Bank Account</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$transaction->bank_rek}}</p>
                        </div>
                    </div>
                    

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Bank Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputBankName" value="{{@$data->Bank  ? $data->Bank->bank_name : ''}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Sender Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputSenderName" value="{{$data->nama_pengirim == '-' ? '' : $data->nama_pengirim}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Payment Date</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="datePaid" value="{{$transaction->transaction_payment}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Payment Type</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="Type" value="{{$transaction->transaction_type}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Status</label>
                        <div class="col-sm-10">
                            <p class="form-control bg-success bg-opacity-10">{{$transaction->payment_status}}</p>
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

            </div>
        </div>
    </section>


@endsection
