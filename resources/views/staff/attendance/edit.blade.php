@extends('Master.master')

@section('title', 'Attendance')

@section('content')

    <div class="pagetitle">
        <h1>Attendance</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <form action="{{ staff_route('attendance.update', $schedule->id) }}" method="post">
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
                            @foreach ($students as $c)
                                @php($d = $details->get($c->id))
                                <tr>
                                    <td>{{ $c->nis }}</td>
                                    <td>{{ $c->nama }}</td>
                                    @if ($mustPay->has($c->id))
                                        <td colspan="3">Please Completed Payment</td>
                                    @else
                                        <td>
                                            <input type="hidden" value="{{ $c->id }}" name="student_id[{{ $loop->index }}]">
                                            <input type="hidden" name="check[{{ $loop->index }}]" value="off">
                                            <input type="checkbox" name="check[{{ $loop->index }}]" class="form-check-input"
                                                value="on" @checked(! $d || $d->Description === 'Attend')>
                                        </td>
                                        <td>
                                            <select name="keterangan[{{ $loop->index }}]" class="form-select">
                                                @if ($d)
                                                    @foreach (['Attend', 'Absent', 'Permission', 'Sick'] as $option)
                                                        <option value="{{ $option }}" @selected($d->Description === $option)>{{ $option }}</option>
                                                    @endforeach
                                                @else
                                                    <option selected>Select...</option>
                                                    <option value="Absent">Absent</option>
                                                    <option value="Permission">Permission</option>
                                                    <option value="Sick">Sick</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="notes[{{ $loop->index }}]" class="form-control" maxlength="255" value="{{ $d?->Notes }}">
                                        </td>
                                    @endif
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
