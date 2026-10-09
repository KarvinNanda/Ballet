@extends('Master.master')
@inject('carbon', 'Carbon\Carbon')

@section('title','Class List')

@section('content')

    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <form action="{{route('getAbsen',$view)}}" method="post">
                @csrf
            <input type="hidden" name="return_url" value="{{$return_url}}">
            <div class="card-body">



                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">NIS</th>
                        <th scope="col">Name</th>
                        <th scope="col">Attend</th>
                        <th scope="col">Description</th>
                        <th scope="col">Notes</th>
                        <th scope="col">Quota</th>

                    </tr>
                    </thead>
                    <tbody>
                        @foreach($class as $c)
                            @php
                                $mustPay = ! @$detail && \App\Support\AttendancePaymentGate::requiresPayment($c, $view->date, $class_name);
                            @endphp
                            <tr>
                                <td>{{$c->nis}}</td>
                                <td>{{$c->nama}}</td>
                                @if($mustPay)
                                    <td colspan="3">Please Completed Payment</td>
                                @else
                                    <td>
                                        <input type="hidden" value="{{$c->id}}" name="student_id[{{$loop->index}}]">
                                        @if(!@$detail)
                                            <input type="hidden" name="check[{{$loop->index}}]" value="off">
                                            <input type="checkbox" name="check[{{$loop->index}}]" class="form-check-input" id="test" value="on" checked>
                                        @elseif(@$detail[$loop->iteration-1]->Description == "Masuk")
                                            <input type="checkbox" name="check[{{$loop->index}}]" class="form-check-input" id="test" value="on" checked disabled>
                                        @else
                                            <input type="checkbox" name="check[{{$loop->index}}]" class="form-check-input" id="test" value="on" disabled>
                                        @endif
                                    </td>
                                    <td >
                                        @if(!@$detail)
                                            <select value="" name="keterangan[{{$loop->index}}]" class="form-select">
                                                <option selected>Select...</option>
                                                <option value="Absent">Absent</option>
                                                <option value="Permission">Permission</option>
                                                <option value="Sick">Sick</option>
                                            </select>
                                        @else
                                            <select value="" name="keterangan[{{$loop->index}}]" class="form-select" disabled>
                                                <option selected> {{@$detail[$loop->iteration-1]->Description}}</option>
                                            </select>
                                        @endif
                                    </td>
                                    <td >
                                        @if(!@$detail)
                                            <input type="text" name="notes[{{$loop->index}}]" class="form-control" maxlength="255">
                                        @else
                                            <input type="text" name="notes[{{$loop->index}}]" class="form-control" value="{{@$detail[$loop->iteration-1]->Notes}}" disabled>
                                        @endif

                                    </td>
                                @endif
                                @if (str_contains($class_name,'Intensive'))
                                <td>{{$c->Quota}} / {{$c->MaxQuota == 0 ?  12 : $c->MaxQuota}}</td>    
                                @elseif(str_contains($class_name,'Pointe'))
                                <td>{{$c->Quota}} / {{$c->MaxQuota == 0 ?  4 : $c->MaxQuota}}</td>    
                                @endif
                                <td>{{$c->Quota}} / {{$c->MaxQuota == 0 ?  3 : $c->MaxQuota}}</td>    
                            </tr>


                        @endforeach

                    </tbody>
                </table>
            </div>
                @if(!@$detail)
                    <div class=" mt-3 mb-3 w-100 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success me-5 mt-2 mb-2">Submit</button>
                    </div>
                @endif
            </form>
        </div>
    </section>


@endsection
