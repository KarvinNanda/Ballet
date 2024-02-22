@extends('Master.master')

@section('title','Detail Class')

@section('content')

    <div class="pagetitle">
        <h1>Detail Class</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <a href="{{route('headViewaddTeacherClass',$id)}}"><button class="btn btn-info me-5 mt-2 mb-2" type="Submit">Add Teacher</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">DOB</th>
                            <th scope="col">Address</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($teachers->isEmpty())
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                        @else
                            @foreach($teachers as $teacher)
                                <tr>
                                    <td>{{$teacher->teacherName}}</td>
                                    <td>{{\Carbon\Carbon::parse($teacher->teacherDOB)->format('d M Y')}}</td>
                                    <td>{{$teacher->teacherAddress}}</td>
                                    <td>{{$teacher->teacherPhone}}</td>
                                    <td>{{$teacher->teacherEmail}}</td>
                                        <td>
                                            <form action="{{route('headClassDeleteTeacher',['teacher' => $teacher->id,'class' => $id])}}" method="get">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </td>
                                </tr>
                            @endforeach
                        @endif


                        </tbody>
                    </div>
                </table>
                <!-- End Table with stripped rows -->
                <div class="alert text-center" role="alert">
                    {{$teachers->links()}}
                </div>

            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="search-bar mt-3 ms-3 mb-3 w-100 d-flex justify-content-between">
                <a href="{{route('headViewaddStudentClass',$id)}}"><button class="btn btn-info me-5 mt-2 mb-2" type="Submit">Add Student</button></a>
                <a href="{{route('headResetQuota',$id)}}"><button class="btn btn-secondary me-5 mt-2 mb-2" type="Submit"> Reset Quota</button></a>
            </div>
            <div class="card-body">

                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <div class="container">
                        <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">DOB</th>
                            <th scope="col">Address</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Quota</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($students->isEmpty())
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                            <td>No Data</td>
                        @else
                            @foreach($students as $student)
                                @php
                                    if($class_name == 'Pointe Class') $quota_pay = 4;
                                    else if($class_name == 'Intensive Kids' || $class_name == 'Intensive Class')$quota_pay = 12;
                                    else $quota_pay = 3;


                                    // if(count($transactions)){
                                    //     foreach ($transactions as $t ) {
                                    //         if($student->id == $t->students_id){
                                    //             if(!is_null($t->paid))$quota_pay = $t->paid;
                                    //             break;
                                    //         }
                                    //     }
                                    // }
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{route('detailStudent',['id' => $student->id])}}">{{$student->studentName}}</a>
                                    </td>
                                    <td>{{\Carbon\Carbon::parse($student ->studentDOB)->format('d M Y')}}</td>
                                    <td>{{$student->studentAddress}}</td>
                                    <td>{{$student->studentEmail}}</td>
                                    <td>{{$student->studentPhone}}</td>
                                    @if($student->studentStatus != 'trial')
                                    <td>{{$student->studentQuota}} / {{$student->studentMaxQuota == 0 ? $quota_pay : $student->studentMaxQuota}}</td>
                                    @else 
                                    <td>{{$student->studentQuota}} / 2 </td>
                                    @endif
                                    {{-- @if($class_name == 'Pointe Class')
                                    @elseif($class_name == 'Intensive Kids' || $class_name == 'Intensive Class')
                                        <td>{{$student->studentQuota}} / {{$quota_pay}}</td>
                                    @else
                                        <td>{{$student->studentQuota}} / {{$quota_pay}}</td>
                                    @endif --}}
                                    <td class="d-flex">
                                        <form action="{{route('headClassDeleteStudent',['student' => $student->id,'class' => $id])}}" method="get">
                                            @csrf
                                            <button type="submit" class="btn btn-danger me-2">Delete</button>
                                        </form>

                                        <form action="{{route('headClassGenerateTransactionStudent',['student' => $student->id,'class' => $id])}}" method="post">
                                            @csrf
                                            <button type="submit" class="btn btn-info">Generate Transaction</button>
                                        </form>
                                    </td>
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
    </section>




@endsection
