@extends('Master.master')

@section('title','Change Password')

@section('content')

    <div class="pagetitle">
        <h1>Change Password</h1>
    </div><!-- End Page Title -->

    <section class="section profile">
        <div class="row d-flex justify-content-center ">

            <div class="col-xl-8 w-100">

                <div class="card">
                    <div class="card-body pt-3">
                        <!-- Bordered Tabs -->

                        <div class="tab-content pt-2">

                            <div class="tab-pane fade show active profile-change-password pt-3" id="profile-change-password">
                                <!-- Change Password Form -->
                                <form action="{{route('change-password',$user)}}" method="post">
                                    @csrf

                                    <div class="row mb-3">
                                        <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="new_password" type="password" class="form-control" id="newPassword">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Confirm Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="confirm_password" type="password" class="form-control" id="renewPassword">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary mb-3">Change Password</button>
                                    </div>

                                    @if($errors->any())
                                        @foreach($errors->all() as $error)
                                            <div class="alert alert-danger" role="alert">
                                                {{$error}}
                                            </div>
                                        @endforeach
                                    @endif

                                </form><!-- End Change Password Form -->

                            </div>

                        </div><!-- End Bordered Tabs -->

                    </div>
                </div>

            </div>
        </div>
    </section>

    </main><!-- End #main -->
@endsection
