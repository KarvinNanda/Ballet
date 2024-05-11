@extends('Master.master')

@section('title','Stock')

@section('content')

    <div class="pagetitle">
        <h1>Buying Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped mb-5">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Size</th>
                            <th scope="col">Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{$stock->name}}</td>
                            <td>{{$stock->size}}</td>
                            <td>{{$stock->quantity}}</td>
                        </tr>
                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->

                <div class="mb-3">
                    <h5>Please fill form below</h5>
                </div>

                <div class="w-50 justify-content-end">
                    <form action="{{route('buying',$stock->id)}}" method="post">
                        @csrf
                        <input type="hidden" name="return_url" value="{{$return_url}}">
                        <div class="row mb-3">
                            <label for="name" class="col-sm-2 col-form-label">Name</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="name">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="qty" class="col-sm-2 col-form-label">Quantity</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="qty" id="qty">
                                <p id="msg"></p>
                            </div>
                        </div>

                        <div class="justify-content-center d-flex">
                            <button class="btn btn-success p-2 ps-5 pe-5 mb-3" id="submit">
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

                    </form>
                </div>

            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            $('#qty').keyup(function(){
                if($(this).val() > {{$stock->quantity}}){
                    $('#msg').text('Quantity input is exceed of quantity item').css("color", "red");
                    $('#submit').hide();
                } else {
                    $('#msg').text('');
                    $('#submit').show();
                }
            })
        });
    </script>

@endsection
