@extends('layouts.app')

@section('title', 'Teacher reward report')

@section('content')
    <x-page-header title="Teacher reward report" />

    <div class="card">
        <div class="card-body">
            @if ($data->isEmpty())
                <x-empty-state icon="calendar-x" title="No months with attendance yet" />
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Month</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->month }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ staff_route('report.teacher.print', $item->month_num) }}" target="_blank">
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
