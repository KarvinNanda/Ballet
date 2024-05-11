@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Admin List')

@section('content')
<div class="d-none">
    {{ $keyword = request('search') }}
</div>
    <div class="pagetitle">
        <h1>Admin Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchAdmin')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('headAdminAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Admin</button></a>
            </div>
            <div class="card-body">


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">DOB</th>
                        <th scope="col">Address</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td>{{$admin->name}}</td>
                        <td>{{$carbon::parse($admin->dob)->format('d M Y')}}</td>
                        <td>{{$admin->address}}</td>
                        <td>{{$admin->phone}}</td>
                        <td>{{$admin->email}}</td>
                        <td class="d-flex">
                            <form action="{{route('AdminDelete',$admin)}}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-danger me-3">Delete</button>
                            </form>
                            <form action="{{route('headAdminUpdatePage',$admin)}}" method="get">
                                <button type="submit" class="btn btn-warning">Update</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$admins->appends(['search' => $keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
