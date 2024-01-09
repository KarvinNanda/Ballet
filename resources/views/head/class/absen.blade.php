@extends('Master.master')

@section('title', 'Attendence')

@section('content')

    <div class="pagetitle">
        <h1>Attendence</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <form action="{{ route('headGetAbsen', $view) }}" method="post">
                @csrf

                <div class="card-body">



                    <!-- Table with stripped rows -->
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">NIS</th>
                                <th scope="col">Name</th>
                                <th scope="col">Action</th>
                                <th scope="col">Description</th>
                                <th scope="col">Notes</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($class as $c)
                                <tr>
                                    <input type="hidden" value="{{ $c->nis }}" name="nis[]">
                                    <td>{{ $c->nis }}</td>
                                    <td>{{ $c->nama }}</td>
                                    <td>
                                        @if (!@$detail)
                                            <input type="hidden" name="check[{{ $loop->iteration - 1 }}]" value="off">
                                            <input type="checkbox" name="check[{{ $loop->iteration - 1 }}]"
                                                class="form-check-input" id="test" value="on" checked>
                                        @elseif(@$detail[$loop->iteration - 1]->Description == 'Masuk')
                                            <input type="hidden" name="check[{{ $loop->iteration - 1 }}]" value="off">
                                            <input type="checkbox" name="check[{{ $loop->iteration - 1 }}]"
                                                class="form-check-input" id="test" value="on" checked>
                                        @else
                                            <input type="hidden" name="check[{{ $loop->iteration - 1 }}]" value="off">
                                            <input type="checkbox" name="check[{{ $loop->iteration - 1 }}]"
                                                class="form-check-input" id="test" value="on">
                                        @endif
                                    </td>
                                    <td>
                                        <select value="" name="keterangan[]" class="form-select">
                                            @if (@$detail)
                                                <option selected value="{{ $detail[$loop->iteration - 1]->Description == 'Masuk' ? 'Attend' : $detail[$loop->iteration - 1]->Description }}">
                                                    {{ @$detail[$loop->iteration - 1]->Description ? ($detail[$loop->iteration - 1]->Description == 'Masuk' ? 'Attend' : $detail[$loop->iteration - 1]->Description) : 'Select...' }}
                                                </option>
                                                @if ($detail[$loop->iteration - 1]->Description == 'Absent')
                                                    <option value="Attend">Attend</option>
                                                    <option value="Permission">Permission</option>
                                                    <option value="Sick">Sick</option>
                                                @elseif($detail[$loop->iteration - 1]->Description == 'Permission')
                                                    <option value="Attend">Attend</option>
                                                    <option value="Absent">Absent</option>
                                                    <option value="Sick">Sick</option>
                                                @elseif($detail[$loop->iteration - 1]->Description == 'Sick')
                                                    <option value="Attend">Attend</option>
                                                    <option value="Absent">Absent</option>
                                                    <option value="Permission">Permission</option>
                                                @else
                                                    <option value="Absent">Absent</option>
                                                    <option value="Permission">Permission</option>
                                                    <option value="Sick">Sick</option>
                                                @endif
                                                @else 
                                                    <option selected>Select...</option>
                                                    <option value="Absent">Absent</option>
                                                    <option value="Permission">Permission</option>
                                                    <option value="Sick">Sick</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        @if (!@$detail)
                                            <input type="text" name="notes[]" class="form-control">
                                        @else
                                            <input type="text" name="notes[]" class="form-control"
                                                value="{{ @$detail[$loop->iteration - 1]->Notes }}">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <div class=" mt-3 mb-3 w-100 d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning me-5 mt-2 mb-2">Submit</button>
                </div>
            </form>
        </div>
    </section>


@endsection
