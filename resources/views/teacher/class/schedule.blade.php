@inject('carbon', 'Carbon\Carbon')
@extends('Master.master')

@section('title','View Class')

@section('content')
<div class="pagetitle">
    <h1>Class Tables</h1>
</div><!-- End Page Title -->

<section class="section">
    <div class="card">

        <div class="card-body">
            <!-- Table with stripped rows -->
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">Class Name</th>
                    <th scope="col">Schedule Detail</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                        <tr>
                            <td>
                                {{$class->Type->class_name}} -
                                {{$class->mapping[0]->getUser->name}}
                                - {{$class->people_count}}
                            </td>
                            <td>
                                <form action="{{route('viewScheduleClassTeacher', ['id' => $class->id])}}" method="get">
                                    <button type="submit" class="btn btn-secondary">Schedule</button>
                                </form>
                            </td>
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
