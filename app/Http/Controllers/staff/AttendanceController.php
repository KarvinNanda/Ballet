<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\RecordAttendanceRequest;
use App\Models\Schedule;
use App\Support\AttendancePaymentGate;
use App\Support\AttendanceRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Head records or corrects the attendance of one schedule (admin is refused by Gate attendance.record). */
class AttendanceController extends Controller
{
    public function edit(Schedule $schedule)
    {
        Gate::authorize('attendance.record');

        $className = (string) DB::table('class_transactions as ct')
            ->join('class_types as t', 't.id', 'ct.class_type_id')
            ->where('ct.id', $schedule->class_id)
            ->value('t.class_name');

        $students = DB::table('mapping_class_children')
            ->join('students', 'students.id', 'mapping_class_children.student_id')
            ->selectRaw('students.id as id, students.nis as nis, students.LongName as nama, students.Quota, students.MaxQuota')
            ->where('mapping_class_children.class_id', $schedule->class_id)
            ->where('students.Status', 'aktif')
            ->get();

        $headerId = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $details = $headerId === null ? collect() : DB::table('detail_absens')->where('header_absen_id', $headerId)->get()->keyBy('student_id');

        // A student without a record yet is refused on save when the payment gate applies; show it on the form too.
        $mustPay = $students->filter(fn ($s) => ! $details->has($s->id) && AttendancePaymentGate::requiresPayment($s, $schedule->date, $className))
            ->pluck('id')->flip();

        return view('staff.attendance.edit', compact('schedule', 'students', 'details', 'mustPay'));
    }

    public function update(RecordAttendanceRequest $req, Schedule $schedule)
    {
        // A new header is credited to the class's first mapped teacher (same as before the merge).
        $teacherId = DB::table('mapping_class_teachers')->where('class_id', $schedule->class_id)->value('user_id');
        $rows = AttendanceRecorder::rowsFromRequest($req->validated());
        $saved = AttendanceRecorder::record($schedule, $rows, $teacherId, allowEdit: true);

        return redirect(staff_route('schedule.index', $schedule->class_id))
            ->with('msg', AttendanceRecorder::message('Success Update Attendance', count($rows), $saved));
    }
}
