@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','Finance List')

@section('content')
<div class="d-none">
    {{ $keyword = request('search') }}
</div>

    <div class="pagetitle">
        <h1>Finance Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('searchFinance')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                <a href="{{route('headFinanceAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Finance</button></a>
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
                    @foreach($finances as $finance)
                    <tr>
                        <td>{{$finance->name}}</td>
                        <td>{{$carbon::parse($finance->dob)->format('d M Y')}}</td>
                        <td>{{$finance->address}}</td>
                        <td>{{$finance->phone}}</td>
                        <td>{{$finance->email}}</td>
                        <td class="d-flex">
                            <form action="{{route('FinanceDelete',$finance)}}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-danger me-3">Delete</button>
                            </form>
                            <form action="{{route('headFinanceUpdatePage',$finance)}}" method="get">
                                <button type="submit" class="btn btn-warning">Update</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$finances->appends(['search' => $keyword])->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
