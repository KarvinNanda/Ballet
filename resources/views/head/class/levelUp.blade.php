@extends('Master.master')

@section('title','Teacher List')

@section('content')
    <form action="{{route('headLevelUpStudent')}}" method="post">
        @csrf
        <input type="hidden" name="class_id" value="{{$class_id}}">
    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
               <button class="btn btn-success me-5 mt-2 mb-2" type="Submit">
                       Level Up
               </button>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($students->isEmpty())
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                        @else
                            @foreach($students as $student)
                                <tr>
                                    <td>{{$student->studentName}}</td>
                                    <td>{{$student->studentEmail}}</td>
                                </tr>
                            @endforeach
                        @endif


                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$students->links()}}
                </div>

            </div>
        </div>
    </form>
    </section>




@endsection
