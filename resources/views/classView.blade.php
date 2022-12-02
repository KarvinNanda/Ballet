

@extends('Master.master')

@section('title','AdminPage')

@section('content')
    <div class="pagetitle">
        <h1>General Tables</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item">Tables</li>
                <li class="breadcrumb-item active">General</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Teacher </h5>
                    <a href="/viewTeacherForm"><button class="btn btn-success me-5"> Add Teacher</button></a>
                </div>


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Class Name</th>
                        <th scope="col">Teacher</th>
                        <th scope="col">Student Count</th>
                        <th scope="col">View</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Brocoli</td>
                        <td>Karvin Nanda</td>
                        <td>30</td>
                        <td>
                            <button class="btn btn-success">View</button>
                        </td>
                        <td>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Students </h5>
                    <a href="viewStudentForm"><button class="btn btn-success me-5"> Add Student</button></a>
                </div>


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Class Name</th>
                        <th scope="col">Teacher</th>
                        <th scope="col">Student Count</th>
                        <th scope="col">View</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Brocoli</td>
                        <td>Karvin Nanda</td>
                        <td>30</td>
                        <td>
                            <button class="btn btn-success">View</button>
                        </td>
                        <td>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">1</th>
                        <td>Brocoli</td>
                        <td>Karvin Nanda</td>
                        <td>30</td>
                        <td>
                            <button class="btn btn-success">View</button>
                        </td>
                        <td>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->

            </div>
        </div>
    </section>


@endsection
