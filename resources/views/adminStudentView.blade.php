

@extends('Master.master')

@section('title','AdminPage')

@section('content')
    <div class="pagetitle">
        <h1>Student Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Student</h5>
                    <a href="/viewTeacherForm"><button class="btn btn-success me-5"> Add Student</button></a>
                </div>


                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Address</th>
                        <th scope="col">DOB</th>
                        <th scope="col">Salary</th>
                        <th scope="col">Update</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Karvin Nanda</td>
                        <td>Karvin@gmail.com</td>
                        <td>0812352345234234</td>
                        <td>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias aliquam asperiores atque culpa, deserunt distinctio, dolor dolores doloribus, dolorum eaque error est et eveniet excepturi fugiat fugit harum ipsum iste laboriosam molestias neque nulla officia perferendis quibusdam quis quod rerum saepe sequi sint suscipit tempore vel veritatis voluptatibus? Blanditiis, ipsa.</td>
                        <td>03-08-20</td>
                        <td>1000000000</td>

                        <td>
                            <button class="btn btn-warning text-white">Update</button>
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
