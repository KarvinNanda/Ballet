@extends('layouts.app')

@section('title', 'Rules')

@section('content')
    <x-page-header title="Rules & regulations">
        <x-slot:actions>
            <a href="{{ route('RulesAddPage') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add rule</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($rules->isEmpty())
                <x-empty-state icon="file-text" title="No rules yet">
                    <x-slot:action>
                        <a href="{{ route('RulesAddPage') }}" class="btn btn-primary">Add rule</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Language</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rules as $rule)
                        <tr>
                            <td>{{ $rule->lang }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('RulesUpdatePage', $rule->id) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                <x-row-menu :label="'More actions for the '.$rule->lang.' rule'">
                                    <li>
                                        <x-confirm-form :action="route('RulesDelete', $rule->id)" :message="'Delete the '.$rule->lang.' rule? This cannot be undone.'">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $rules->links() }}</div>
            @endif
        </div>
    </div>
@endsection
