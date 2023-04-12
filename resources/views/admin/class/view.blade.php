@extends('Master.master')

@section('title','Class List')

@section('content')
    <div class="pagetitle">
        <h1>Class Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="dropdown mt-3 ms-3">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Choose
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{route('adminActiveClassPage')}}">Active</a></li>
                    <li><a class="dropdown-item" href="{{route('adminNonActiveClassPage')}}">Non-Active</a></li>
                </ul>
            </div>
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <form class="search-form d-flex align-items-center" method="POST" action="{{route('adminSearchClass')}}">
                    @csrf
                    <input type="text" name="search" placeholder="Search" title="Enter search keyword">
                </form>
                    <a href="{{route('adminClassAddPage')}}"><button class="btn btn-success me-5 mt-2 mb-2"> Add Class</button></a>
            </div>
            <div class="card-body">



                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Class Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Action</th>
                        <th scope="col">Detail</th>
                        <th scope="col">Schedule Detail</th>
                        <th scope="col">Level Up</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classes as $class)
                        <tr>
                            <td>
                                {{$class->Type->class_name}} -
                                @foreach ($class->mapping as $map)
                                {{$map->getUser->name}} 
                            @endforeach
                            - {{$class->id}}
                            </td>
                            <td>Rp.{{number_format($class->Type->class_price)}}</td>
                            <td>
                                <form action="{{route('changeStatusClassAdmin',$class)}}" method="post">
                                    @csrf
                                    @if($class->Status == 'aktif')
                                        <button type="submit" class="btn btn-primary">Deactive</button>
                                    @else
                                        <button type="submit" class="btn btn-primary">Active</button>
                                    @endif
                                </form>
                            </td>
                            @if($class->Status == 'aktif')
                            <td>
                                <form action="{{route('adminDetailClass')}}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{$class->id}}" name="classId">
                                    <button type="submit" class="btn btn-secondary">Detail</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{route('viewScheduleClass')}}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{$class->id}}" name="classId">
                                    <button type="submit" class="btn btn-secondary">Schedule</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{route('levelUp')}}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{$class->id}}" name="classId">
                                    <button type="submit" class="btn btn-success">Level Up</button>
                                </form>
                            </td>
                            {{-- <td>
                                <form action="{{route('resetClass',$class->id)}}" onclick="return confirm('Are you sure?')" method="get">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Reset Class</button>
                                </form>
                            </td> --}}
                            @else
                                <td>None</td>
                                <td>None</td>
                                <td>None</td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$classes->links()}}
                </div>
            </div>
        </div>
    </section>


@endsection
