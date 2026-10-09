@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Schedules" subtitle="Sessions of active classes up to 7 days ahead, latest first." />

    <section class="card">
        <div class="card-body">
            @if ($data->isEmpty())
                <x-empty-state icon="calendar3" title="No upcoming or recent sessions" />
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Teacher</th>
                                <th scope="col">Class</th>
                                <th scope="col">Day</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                @php($date = \Illuminate\Support\Carbon::parse($item->date))
                                <tr>
                                    <td>{{ $item->teacherName }}</td>
                                    <td>{{ $item->class }}</td>
                                    <td>{{ $date->englishDayOfWeek }}</td>
                                    <td>{{ $date->format('d M Y') }}</td>
                                    <td>{{ $date->format('H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $data->links() }}</div>
            @endif
        </div>
    </section>
@endsection
