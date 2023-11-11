@inject('carbon','Carbon\Carbon')
@extends('Master.master')

@section('title','Student List')

@section('content')
    <div class="d-none">
        {{ $keyword = request('keyword') }}
        {{ $status = request('status', 'all') }}
    </div>

    <div class="pagetitle">
        <h1>Student Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-2 mb-3 w-100 d-flex justify-content-between">
                <form
                    class="d-flex align-items-center justify-content-center gap-2"
                    method="GET"
                    action="{{route('headStudentPage')}}"
                >
                    <input class="form-control" type="text" value="{{$keyword}}" name="keyword" placeholder="Search">

                    @csrf
                    <select class="form-select" name="status">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                        <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Active</option>
                        <option value="non-aktif" {{ $status == 'non-aktif' ? 'selected' : '' }}>Non Active</option>
                    </select>

                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filters</button>
                </form>

                <a href="{{route('headStudentAddPage')}}">
                    <button class="btn btn-success me-3 mb-3">Add Student</button>
                </a>
            </div>

            <div class="card-body">
                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                    <thead>
                    <tr>
                        <th scope="col"><a href="{{route("sortingStudent",['value' => 'name','type' => $sort])}}">Name</a></th>
                        <th scope="col"><a href="{{route("sortingStudent",['value' => 'dob','type' => $sort])}}">DOB</a></th>
{{--                        <th scope="col"><a href="{{route("sortingStudent",['value' => 'age','type' => $sort])}}">Age</a></th>--}}
                        <th scope="col">Parent</th>
                        <th scope="col">Account Number</th>
                        <th scope="col">Sender</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Status</th>
                        <th colspan="3" class="text-center" scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{$student->name}}</td>
                            <td>{{$carbon::parse($student->dob)->format('d M')}} ({{$student->age}})</td>
{{--                            <td>{{$student->age}}</td>--}}
                            <td>{{$student->ortu}}</td>
                            <td>{{$student->rek}}</td>
                            <td>{{$student->pengirim}}</td>
                            <td>{{$student->phone}}</td>
                            <td>
                                <form action="{{route('nonactiveStudent',$student->id)}}" method="post">
                                    @csrf
                                    @if($student->status == 'aktif')
                                        <input type="submit" name="stats" class="btn btn-primary mb-2" value="Inactive"> <br>
                                        <input type="submit" name="stats" class="btn btn-info" value="Trial">
                                    @elseif($student->status == 'non-aktif')
                                        <input type="submit" name="stats" class="btn btn-primary mb-2" value="Active"> <br>
                                        <input type="submit" name="stats" class="btn btn-info" value="Trial">
                                    @else
                                        <input type="submit" name="stats" class="btn btn-primary mb-2" value="Active"> <br>
                                        <input type="submit" name="stats" class="btn btn-primary" value="Inactive">
                                    @endif
                                </form>
                            </td>
                            <td>
                                <form action="{{route('deleteStudent',$student->id)}}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{route('detailStudent',$student->id)}}" method="get">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">Detail</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
            </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{
                        $students->appends([
                            'status' => request('status'),
                            'keyword' => request('keyword')
                        ])->links()
                    }}
                </div>
            </div>
        </div>
    </section>
@endsection
