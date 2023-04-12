@extends('Master.master')

@section('title','Class Type List')

@section('content')
    <div class="pagetitle">
        <h1>Class Type Tables</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <!-- Table with stripped rows -->
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Class Type</th>
                        <th scope="col">Price</th>
                        <th scope="col">Update</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($types as $type)
                        <tr>
                            <td>
                                {{$type->class_name}}
                            </td>
                            <td>Rp. {{$type->class_price}}</td>
                            <td>
                                <form action="{{route('adminViewChangeTypeClass')}}" method="post">
                                     @csrf
                                     <input type="hidden" name="typeID" value="{{$type->id}}">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>


@endsection
