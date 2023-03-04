@extends('Master.master')

@section('title','Change Profile')

@section('content')


    <div class="pagetitle">
        <h1>My Profile</h1>
    </div><!-- End Page Title -->

    <section class="section profile">
        <div class="row d-flex justify-content-center ">

            <div class="col-xl-8 w-100">

                <div class="card">
                    <div class="card-body pt-3">
                        <!-- Bordered Tabs -->

                        <div class="tab-content pt-2">

                            <div class="tab-pane fade show active profile-edit pt-3" id="profile-edit">

                                <!-- Profile Edit Form -->
                                <form action="{{route('change-profile',$user)}}" method="post">
                                    @csrf


                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                                        <div class="col-md-8 col-lg-9">
{{--                                            <input name="fullName" type="text" class="form-control" id="fullName" value="Kevin Anderson">--}}
                                            {{$user->name}}
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="about" class="col-md-4 col-lg-3 col-form-label">Tanggal Lahir</label>
                                        <div class="col-md-8 col-lg-9">
{{--                                            <textarea name="about" class="form-control" id="about" style="height: 100px">Sunt est soluta temporibus accusantium neque nam maiores cumque temporibus. Tempora libero non est unde veniam est qui dolor. Ut sunt iure rerum quae quisquam autem eveniet perspiciatis odit. Fuga sequi sed ea saepe at unde.</textarea>--}}
                                            {{\Carbon\Carbon::parse($user->dob)->format('d M Y')}}
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="address" type="text" class="form-control" id="Address" value="{{$user->address}}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="phone" type="text" class="form-control" id="Phone" value="{{$user->phone}}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="text" class="form-control" id="Email" value="{{$user->email}}">
                                        </div>
                                    </div>



                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary mb-3">Save Changes</button>
                                    </div>

                                    @if($errors->any())
                                        @foreach($errors->all() as $error)
                                            <div class="alert alert-danger" role="alert">
                                                {{$error}}
                                            </div>
                                        @endforeach
                                    @endif

                                </form><!-- End Profile Edit Form -->

                            </div>

                        </div><!-- End Bordered Tabs -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main><!-- End #main -->
@endsection
