@extends('Master.master')

@section('title','Home')

@section('content')
    <div class="pagetitle">
        <h1>General Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Table with stripped rows </h5>
                    <a href=""><button class="btn btn-success me-5"> Add Class</button></a>
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
                            <a href="/viewClass">
                                <button class="btn btn-success">View</button>
                            </a>
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
