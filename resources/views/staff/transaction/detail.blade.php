@inject('carbon','Carbon\Carbon')
@extends('Master.master')

@section('title','Detail Transaction')

@section('content')
    <div class="pagetitle">
        <h1>Transaction Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->LongName}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Due Date</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->transaction_date}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Class</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->class_name}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Price</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10" id="price">Rp.{{number_format($detail->price)}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Discount</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10" id="disc">{{$detail->discount == 0 ? 0 :$detail->discount}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputAddress" class="col-sm-2 col-form-label">Description</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->desc == null ? '' : $detail->desc}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Total</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10" id="total">Rp.{{0}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Quota</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10" id="total">{{$detail->transaction_quota}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label  class="col-sm-2 col-form-label">Account Number</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->bank_rek}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label  class="col-sm-2 col-form-label">Bank Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{@$data->Bank  ? $data->Bank->bank_name : ''}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label  class="col-sm-2 col-form-label">Sender Name</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{@$data ? $data->nama_pengirim : ''}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputDOB" class="col-sm-2 col-form-label">Payment Date</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->transaction_payment}}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label  class="col-sm-2 col-form-label">Payment Type</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->transaction_type}}</p>
                    </div>
                </div>



                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Status</label>
                    <div class="col-sm-10">
                        <p class="form-control bg-success bg-opacity-10">{{$detail->payment_status}}</p>
                    </div>
                </div>


            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {
                let price = $("#price").text().replaceAll("Rp.","").replaceAll(",","");
                let discText = $('#disc').text();
                if(discText.includes("%")){
                    let disc = parseInt(discText.replaceAll("%",""));
                    console.log(disc);
                    let total = price - (price * (disc / 100));
                    let cur = total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }).replaceAll(",00","");
                    $("#total").text(cur);
                } else {
                    let total = price - parseInt(discText);
                    let cur = total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }).replaceAll(",00","");
                    $("#total").text(cur);
                }
        });
    </script>

@endsection
