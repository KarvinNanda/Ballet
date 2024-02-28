@extends('Master.master')

@section('title','Detail Student')

@section('content')
    <div class="pagetitle">
        <h1>Student Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <form class="card" method="post" action="{{route('updateStudent')}}">
            @csrf
            <input type="hidden" name="id" value={{$detail->id}}>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">NIS</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="nis" value="{{$detail->nis}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Long Name</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="LongName" value="{{$detail->LongName}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Nick Name</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="ShortName" value="{{$detail->ShortName}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputName" class="col-sm-2 col-form-label">Age</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="age" value="{{$detail->age}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Email" value="{{$detail->Email}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputDOB" class="col-sm-2 col-form-label">DOB</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="dob" value="{{$detail->dob}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputAddress" class="col-sm-2 col-form-label">Address</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Address" value="{{$detail->Address}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Parent Name</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="nama_orang_tua" value="{{$detail->nama_orang_tua}}">
                    </div>
                </div>

                 @if(@$detail->bank)
                    <div class="row mb-3">
                        <label for="inputPhone" class="col-sm-2 col-form-label">Bank Name</label>
                        <div class="col-sm-10">
                            {{-- <p class="form-control bg-success bg-opacity-10">{{$detail->bank}}</p> --}}
                            <input class="form-control bg-opacity-10" name="bank" value="{{$detail->bank}}">
                        </div>
                    </div>
                @endif

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Sender Name</label>
                    <div class="col-sm-10">
                        {{-- <p class="form-control bg-success bg-opacity-10">{{$detail->pengirim}}</p> --}}
                        <input class="form-control bg-opacity-10" name="sender" value="{{$detail->pengirim}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Account Number</label>
                    <div class="col-sm-10">
                            {{-- <p class="form-control bg-success bg-opacity-10">{{$detail->rek}}</p> --}}
                            <input class="form-control bg-opacity-10" name="accountno" value="{{$detail->rek}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">City</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="city" value="{{$detail->City}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Postal Code</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="kode_pos" value="{{$detail->kode_pos}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">First Phone</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Phone1" value="{{$detail->Phone1}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Second Phone</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Phone2" value="{{$detail->Phone2}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Whatsapp</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Whatsapp" value="{{$detail->Whatsapp}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Instagram</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Instagram" value="{{$detail->Instagram}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Line</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Line" value="{{$detail->Line}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Enroll Date</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="EnrollDate" value="{{$detail->EnrollDate}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Quota</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="Quota" value="{{$detail->Quota}}">
                    </div>
                </div>

                {{-- <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Max Quota</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="MaxQuota" value="{{$detail->MaxQuota}}">
                    </div>
                </div> --}}

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">New Student</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="is_new" value="{{$detail->is_new == 1 ? 'Yes' : 'No'}}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputPhone" class="col-sm-2 col-form-label">Status</label>
                    <div class="col-sm-10">
                        <input class="form-control bg-opacity-10" name="status" value="{{$detail->Status}}">
                    </div>
                </div>

                <section class="section">
                    <div class="card">
                        <div class="card-body">
                            <div class="mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                                <h5 class="card-title">Courses Taken</h5>

                                <a class="btn btn-success p-2 ps-3 pe-3 m-3" role="button" href="{{route('headStudentClassAddPage',$detail->id)}}">
                                    Add Class
                                </a>
                            </div>
                            

                            <table class="table">
                                <thead>
                                <th>Course Name</th>
                                <th>Price</th>
                                <th>Quota</th>
                                </thead>
                                <tbody>
                                @foreach ($courses_taken as $data)
                                    @php
                                        if($data->class_name == 'Pointe Class') $quota_pay = 4;
                                        else if($data->class_name == 'Intensive Kids' || $data->class_name == 'Intensive Class')$quota_pay = 12;
                                        else $quota_pay = 3;
                                    @endphp
                                    <tr>
                                        <td>{{$data->class_name}}</td>
                                        <td>{{"Rp.".number_format($data->class_price)}}</td>
                                        <td>{{$data->quota == 0 ? $quota_pay : $data->quota}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Transactions</h5>

                            <table class="table">
                                <thead>
                                <th>Transaction Date</th>
                                <th>Class</th>
                                <th>Total</th>
                                <th>Transaction Payment Date</th>
                                <th>Transaction Status</th>
                                <th>Quota</th>
                                </thead>
                                <tbody>
                                @foreach ($transactions as $trans)
                                    <tr>
                                        <td>
                                            <form action="{{route('detailTransaction',$trans->id)}}" method="get">
                                            </form>
                                            <form action="{{route('detailTransaction',$trans->id)}}" method="get">
                                                <button type="submit" style="border : none; background : none; color:blue;">{{$trans->transaction_date}}</button>
                                            </form>
                                        </td>
                                        <td>{{$trans->class_name}}</td>
                                        @if(str_contains($trans->discount, '%'))
                                        @php
                                            $disc = str_replace("%","",$trans->discount);
                                        @endphp
                                            <td>
                                                Rp.{{number_format($trans->price - (($disc/100)*$trans->price))}}
                                            </td>
                                        @else
                                            <td>Rp.{{number_format($trans->price - $trans->discount)}}</td>
                                        @endif
                                        <td>{{is_null($trans->transaction_payment) ? 'Waiting for Payment' : $trans->transaction_payment}}</td>
                                        <td>{{$trans->payment_status}}</td>
                                        <td>{{is_null($trans->transaction_quota) ? 0 : $trans->transaction_quota}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

            </div>
            @if($errors->any())
                @foreach($errors->all() as $error)
                    <div class="alert alert-danger" role="alert">
                        {{$error}}
                    </div>
                @endforeach
            @endif

            <button type="submit" class="btn btn-success p-2 ps-5 pe-5 m-3">Save Data</button>

        </form>
    </section>


@endsection
