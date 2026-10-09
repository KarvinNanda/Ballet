@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
    @php($search = request('search'))

    <x-page-header title="Teachers">
        <x-slot:actions>
            <a href="{{ staff_route('teacher.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add teacher</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="staff_route('teacher.index')">
        <label for="teacher-search" class="visually-hidden">Search by teacher name</label>
        <input id="teacher-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search teacher name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($teachers->isEmpty())
                <x-empty-state icon="person-badge" title="No teachers found">
                    @if (filled($search))
                        <x-slot:action>
                            <a href="{{ staff_route('teacher.index') }}" class="btn btn-outline-secondary">Reset search</a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Reward %</th>
                        <th scope="col">Age</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($teachers as $t)
                        <tr>
                            <td>{{ $t->name }}</td>
                            <td>{{ $t->percent ?? 0 }}%</td>
                            <td><x-age :dob="$t->dob" /></td>
                            <td>{{ $t->phone ?? '-' }}</td>
                            <td>{{ $t->email }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ staff_route('teacher.edit', $t) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                <x-row-menu :label="'More actions for '.$t->name">
                                    <li><a href="{{ staff_route('teacher.switch', $t) }}" class="dropdown-item">Replace &amp; delete…</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        {{-- A teacher with any class (frozen included) is sent to the Replace page instead (TeacherController::destroy). --}}
                                        <x-confirm-form :action="staff_route('teacher.destroy', $t)" :message="'Delete '.$t->name.'? This cannot be undone.'">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $teachers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
