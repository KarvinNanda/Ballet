@extends('layouts.app')

@section('title', 'Classes')

@section('content')
    @php
        $keyword = request('keyword');
        $status = request('status', 'all');
        $filters = array_filter(['keyword' => $keyword, 'status' => $status === 'all' ? null : $status]);
        $statusTabs = ['all' => 'All', 'aktif' => 'Active', 'non-aktif' => 'Inactive'];
        $sortUrl = fn (string $column) => staff_route('class.sort', array_merge(['column' => $column, 'direction' => $sort], $filters));
    @endphp

    <x-page-header title="Classes">
        <x-slot:actions>
            <a href="{{ staff_route('class.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add class</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="staff_route('class.index')">
        <label for="class-keyword" class="visually-hidden">Search classes</label>
        <input id="class-keyword" class="form-control" type="search" name="keyword" value="{{ $keyword }}" placeholder="Search course, teacher or student…">
        <input type="hidden" name="status" value="{{ $status }}">
        <nav class="status-filter" aria-label="Filter by status">
            @foreach ($statusTabs as $value => $label)
                <a href="{{ staff_route('class.index', array_filter(['keyword' => $keyword, 'status' => $value === 'all' ? null : $value])) }}"
                   class="status-filter-link{{ $status === $value ? ' active' : '' }}" @if ($status === $value) aria-current="page"@endif>{{ $label }}</a>
            @endforeach
        </nav>
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($classes->isEmpty())
                <x-empty-state icon="easel" title="No classes found">
                    <x-slot:action>
                        <a href="{{ staff_route('class.index') }}" class="btn btn-outline-secondary">Reset filters</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col"><a href="{{ $sortUrl('class_name') }}">Class</a></th>
                        <th scope="col">Teacher</th>
                        <th scope="col">Students</th>
                        <th scope="col" class="d-none d-lg-table-cell">Price</th>
                        <th scope="col"><a href="{{ $sortUrl('status') }}">Status</a></th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($classes as $class)
                        @php
                            $teacher = $class->mapping->first()?->getUser?->name;
                            $label = ($class->Type?->class_name ?? 'Class').($teacher ? ' – '.$teacher : '');
                            $active = $class->Status === 'aktif';
                        @endphp
                        <tr>
                            <td>{{ $class->Type?->class_name ?? '-' }}</td>
                            <td>{{ $teacher ?? '-' }}</td>
                            <td>{{ $class->people_count }}</td>
                            <td class="d-none d-lg-table-cell">Rp{{ number_format($class->class_transaction_price) }}</td>
                            <td><x-status-badge :status="$class->Status" /></td>
                            <td class="text-end text-nowrap">
                                @if ($active)
                                    <a href="{{ staff_route('class.show', $class) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    <a href="{{ staff_route('schedule.index', $class->id) }}" class="btn btn-sm btn-outline-secondary">Schedule</a>
                                @endif
                                <x-row-menu :label="'More actions for '.$label">
                                    <li>
                                        <x-confirm-form :action="staff_route('class.status', $class)" :message="'Set '.$label.' to '.($active ? 'Inactive' : 'Active').'?'">
                                            <button type="submit" class="dropdown-item">Set {{ $active ? 'Inactive' : 'Active' }}</button>
                                        </x-confirm-form>
                                    </li>
                                    @if ($active)
                                        <li>
                                            {{-- No confirm here: the next page is the confirmation. --}}
                                            <form action="{{ staff_route('class.level') }}" method="post">
                                                @csrf
                                                <input type="hidden" name="classId" value="{{ $class->id }}">
                                                <button type="submit" class="dropdown-item">Freeze…</button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <x-confirm-form :action="staff_route('class.destroy', $class)" :message="'Delete '.$label.'? This cannot be undone.'">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $classes->links() }}</div>
            @endif
        </div>
    </div>
@endsection
