@extends('layouts.app')

@section('title', 'Finance accounts')

@section('content')
    @php($search = request('search'))

    <x-page-header title="Finance accounts">
        <x-slot:actions>
            <a href="{{ staff_route('finance.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add finance account</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="staff_route('finance.index')">
        <label for="finance-search" class="visually-hidden">Search by name</label>
        <input id="finance-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($finances->isEmpty())
                <x-empty-state icon="wallet2" title="No finance accounts found">
                    <x-slot:action>
                        <a href="{{ staff_route('finance.index') }}" class="btn btn-outline-secondary">Reset search</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Age</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        @can('finance.manage')
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        @endcan
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($finances as $finance)
                        <tr>
                            <td>{{ $finance->name }}</td>
                            <td><x-age :dob="$finance->dob" /></td>
                            <td>{{ $finance->phone ?? '-' }}</td>
                            <td>{{ $finance->email }}</td>
                            @can('finance.manage')
                                <td class="text-end text-nowrap">
                                    <a href="{{ staff_route('finance.edit', $finance) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                    <x-row-menu :label="'More actions for '.$finance->name">
                                        <li>
                                            <x-confirm-form :action="staff_route('finance.destroy', $finance)" :message="'Delete '.$finance->name.'? This cannot be undone.'">
                                                <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                            </x-confirm-form>
                                        </li>
                                    </x-row-menu>
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $finances->links() }}</div>
            @endif
        </div>
    </div>
@endsection
