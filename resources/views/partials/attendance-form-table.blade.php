{{-- Attendance form rows, shared by staff/attendance/edit (head) and teacher/absen.
     $students: id, nis, nama. $details: stored rows keyed by student_id (empty on a new record).
     $mustPay: student ids the payment gate refuses, as keys. $hasHeader (optional): the schedule already has a
     record, so a student without a row is shown as "Not recorded yet". Field names are read by AttendanceRecorder. --}}
@php
    // "Attend" is not a reason: a present student is the ticked box. An empty reason is saved as Absent (AttendanceRecorder).
    $reasons = ['Absent', 'Permission', 'Sick'];
@endphp
<table class="table table-hover">
    <thead>
    <tr>
        <th scope="col">Student</th>
        <th scope="col">Present</th>
        <th scope="col">Reason if absent</th>
        <th scope="col">Notes</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($students as $c)
        @php
            $i = $loop->index;
            $d = $details->get($c->id);
            $stored = $d?->Description;
            // Older rows (and the seed) store Indonesian values; show them as the reason the app writes today.
            $description = ['Sakit' => 'Sick', 'Izin' => 'Permission'][$stored] ?? $stored;
            $legacy = $d && filled($stored) && $description !== 'Attend' && ! in_array($description, $reasons, true);
        @endphp
        <tr>
            <td>
                {{ $c->nama }}
                @if (($hasHeader ?? false) && ! $d)
                    <span class="status-badge status-badge-neutral">Not recorded yet</span>
                @endif
                @if (filled($c->nis))
                    <div class="row-note">NIS {{ $c->nis }}</div>
                @endif
            </td>
            @if ($mustPay->has($c->id))
                <td colspan="3"><span class="status-badge status-badge-warning">Payment required</span></td>
            @else
                <td>
                    <input type="hidden" value="{{ $c->id }}" name="student_id[{{ $i }}]">
                    <input type="hidden" name="check[{{ $i }}]" value="off">
                    {{-- The label wraps the box so the whole 44px cell area is tappable on touch screens. --}}
                    <label class="check-hit" for="present-{{ $i }}">
                        <input type="checkbox" id="present-{{ $i }}" name="check[{{ $i }}]" class="form-check-input" value="on" @checked(! $d || $description === 'Attend')>
                        <span class="visually-hidden">{{ $c->nama }} is present</span>
                    </label>
                </td>
                <td>
                    <label class="visually-hidden" for="reason-{{ $i }}">Reason if {{ $c->nama }} is absent</label>
                    <select id="reason-{{ $i }}" name="keterangan[{{ $i }}]" class="form-select">
                        <option value="" @selected(! in_array($description, $reasons, true))>No reason</option>
                        @foreach ($reasons as $reason)
                            <option value="{{ $reason }}" @selected($description === $reason)>{{ $reason }}</option>
                        @endforeach
                    </select>
                    @if ($legacy)
                        <div class="row-note">Saved as “{{ $stored }}”</div>
                    @endif
                </td>
                <td>
                    <label class="visually-hidden" for="notes-{{ $i }}">Notes for {{ $c->nama }}</label>
                    <input type="text" id="notes-{{ $i }}" name="notes[{{ $i }}]" class="form-control" maxlength="255" value="{{ $d?->Notes }}">
                </td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
