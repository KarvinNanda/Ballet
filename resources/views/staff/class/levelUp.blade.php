@extends('Master.master')

@section('title','Level Up')

@section('content')
    <section class="section">
        <form action="{{staff_route('class.level.store')}}" method="post" data-confirm="Freeze kelas ini?">
            @csrf
            <input type="hidden" name="classId" value="{{$class_id}}">
            <input type="hidden" name="return_url" value="{{$return_url}}">
            <div class="card">
                <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                    <button class="btn btn-info me-5 mt-2 mb-2" type="submit">
                        Level Up
                    </button>
                </div>
                <div class="card-body">

                    <!-- Table with stripped rows -->
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Age</th>
                            <th scope="col">Email</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{$student->studentName}}</td>
                                <td>{{ $student->studentDOB ? \Carbon\Carbon::parse($student->studentDOB)->age : '-' }}</td>
                                <td>{{$student->studentEmail}}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No Data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->

                </div>
            </div>
        </form>
    </section>

@endsection
