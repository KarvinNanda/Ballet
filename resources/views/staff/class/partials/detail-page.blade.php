{{-- Class detail body, shared by the active ($actions = true) and frozen ($actions = false) detail pages. --}}
@php
    $tab = request('tab', request()->has('teachers') ? 'teachers' : 'students') === 'teachers' ? 'teachers' : 'students';
    $tabs = ['students' => 'Students ('.$students->total().')', 'teachers' => 'Teachers ('.$teachers->total().')'];
@endphp

<div class="card summary-card">
    <div class="summary-head">
        <div>
            <h1 class="page-title summary-name">{{ $class_label }}</h1>
            <p class="summary-meta">
                {{ $students->total() === 1 ? '1 student' : $students->total().' students' }} ·
                {{ $teachers->total() === 1 ? '1 teacher' : $teachers->total().' teachers' }}
                @unless ($actions) · Frozen @endunless
            </p>
        </div>
        <x-status-badge :status="$class_status" />
    </div>
</div>

<x-tabs :tabs="$tabs" :active="$tab" />

<div class="tab-content">
    <x-tab-pane name="students" :active="$tab === 'students'">
        <div class="card card-tabbed">
            <div class="card-body">
                @if ($actions)
                    <div class="d-flex flex-wrap justify-content-end gap-2 mb-2">
                        <a href="{{ staff_route('class.student.create', $class_id) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add student</a>
                        <x-confirm-form :action="staff_route('class.reset-quota', $class_id)" message="Reset quota of every student in this class? Quota becomes 0 and MaxQuota is reduced by the used quota.">
                            <button type="submit" class="btn btn-outline-danger btn-sm">Reset quota…</button>
                        </x-confirm-form>
                    </div>
                @endif
                @include('staff.class.partials.students', ['actions' => $actions])
            </div>
        </div>
    </x-tab-pane>

    <x-tab-pane name="teachers" :active="$tab === 'teachers'">
        <div class="card card-tabbed">
            <div class="card-body">
                @if ($actions)
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ staff_route('class.teacher.create', $class_id) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add teacher</a>
                    </div>
                @endif
                @include('staff.class.partials.teachers', ['actions' => $actions])
            </div>
        </div>
    </x-tab-pane>
</div>
