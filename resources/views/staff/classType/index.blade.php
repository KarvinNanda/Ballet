@extends('layouts.app')

@section('title', 'Courses')

@section('content')
    <x-page-header title="Courses">
        <x-slot:actions>
            <a href="{{ staff_route('class.course.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add course</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($types->isEmpty())
                <x-empty-state icon="journal-bookmark" title="No courses yet">
                    <x-slot:action>
                        <a href="{{ staff_route('class.course.create') }}" class="btn btn-primary">Add course</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Course</th>
                        <th scope="col">Price</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td>{{ $type->class_name }}</td>
                            <td>Rp{{ number_format((int) $type->class_price) }}</td>
                            <td class="text-end text-nowrap">
                                {{-- The update page is reached by POST (class-type.edit), so Update is a small form. --}}
                                <form method="post" action="{{ staff_route('class-type.edit') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="typeID" value="{{ $type->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                                </form>
                                <x-row-menu :label="'More actions for '.$type->class_name">
                                    <li>
                                        <x-confirm-form :action="staff_route('class-type.destroy')" :message="'Delete course '.$type->class_name.'? This cannot be undone.'">
                                            <input type="hidden" name="typeID" value="{{ $type->id }}">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $types->links() }}</div>
            @endif
        </div>
    </div>
@endsection
