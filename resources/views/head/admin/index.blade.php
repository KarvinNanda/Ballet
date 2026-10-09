@extends('layouts.app')

@section('title', 'Admin accounts')

@section('content')
    @php($search = request('search'))

    <x-page-header title="Admin accounts">
        <x-slot:actions>
            <a href="{{ route('headAdminAddPage') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add admin</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="route('headAdminPage')">
        <label for="admin-search" class="visually-hidden">Search by admin name</label>
        <input id="admin-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search admin name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($admins->isEmpty())
                <x-empty-state icon="person-gear" title="No admin accounts found">
                    @if (filled($search))
                        <x-slot:action>
                            <a href="{{ route('headAdminPage') }}" class="btn btn-outline-secondary">Reset search</a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Age</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td><x-age :dob="$admin->dob" /></td>
                            <td>{{ $admin->phone ?? '-' }}</td>
                            <td>{{ $admin->email }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('headAdminUpdatePage', $admin) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                <x-row-menu :label="'More actions for '.$admin->name">
                                    <li>
                                        <x-confirm-form :action="route('AdminDelete', $admin)" :message="'Delete admin '.$admin->name.'? This cannot be undone.'">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $admins->links() }}</div>
            @endif
        </div>
    </div>
@endsection
