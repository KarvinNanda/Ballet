@extends('Master.master')

@section('title','Rule List')

@section('content')
<div class="d-none">
    {{ $keyword = request('search') }}
</div>

    <div class="pagetitle">
        <h1>Rules & Regulations Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <div></div>
                <a href="{{route('RulesAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Rule</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Language</th>
                            <th scope="col" colspan="2">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rules as $rule)
                            <tr>
                                <td>{{$rule->lang}}</td>
                                <td class="d-flex">
                                    <form action="{{route('RulesDelete',$rule->id)}}" method="get">
                                        <button type="submit" class="btn btn-danger me-3">Delete</button>
                                    </form>
                                    <form action="{{route('RulesUpdatePage',$rule->id)}}" method="get">
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
                    {{$rules->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
