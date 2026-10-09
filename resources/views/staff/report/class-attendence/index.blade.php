@extends('layouts.app')

@section('title', 'Class attendance report')

@section('content')
    <x-page-header title="Class attendance report" />

    <div class="card">
        <div class="card-body">
            @if ($data->isEmpty())
                <x-empty-state icon="clipboard-check" title="No attendance recorded yet" />
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Class</th>
                        <th scope="col">Teacher</th>
                        <th scope="col">Students</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->class_name }}</td>
                            <td>{{ $item->teacher }}</td>
                            <td>{{ $item->students }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ staff_route('report.class.print', ['header' => $item->class_id, 'teacher' => $item->teacher]) }}" target="_blank">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Report</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
