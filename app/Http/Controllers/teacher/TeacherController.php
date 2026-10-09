<?php

namespace App\Http\Controllers\teacher;

use App\Support\Like;
use App\Http\Controllers\Controller;
use App\Http\Controllers\teacher\Concerns\AuthorizesTeacherClasses;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Teacher\RecordAttendanceRequest;
use App\Models\ClassTransaction;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Support\AttendancePaymentGate;
use App\Support\AttendanceRecorder;
use App\Support\AttendanceWindow;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    use AuthorizesTeacherClasses;

    private const NOT_OPEN = 'Attendance for this session is not open.';

    public function index(SearchRequest $request)
    {
        $keyword = $request->query('keyword');
        $teacherId = (int) $request->user()->id;
        $now = AttendanceWindow::now();
        $date = $now->startOfDay();

        $today = $this->sessionsOn($teacherId, $date->toDateString(), $keyword)
            ->map(fn (object $session) => $this->withStatus($session, $now));
        // Recorded sessions of yesterday are done; the others can still be recorded until the end of today.
        $yesterday = $this->sessionsOn($teacherId, $date->subDay()->toDateString(), $keyword)
            ->reject(fn (object $session) => $session->recorded)
            ->map(fn (object $session) => $this->withStatus($session, $now))
            ->values();

        return view('teacher.index', compact('today', 'yesterday', 'date'));
    }

    public function viewAbsen($id)
    {
        $schedule = $this->authorizeSchedule($id);
        $headerId = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $recorded = $headerId !== null;

        // A recorded session can be viewed at any time; the form opens only inside the attendance window.
        if (! $recorded && ! AttendanceWindow::isOpen($schedule->date)) {
            return back(302, [], route('teacher'))->with('error', self::NOT_OPEN);
        }

        // Back link and the form's return_url: the page that opened this one (backTo() drops foreign URLs).
        $return_url = $this->backTo(url()->previous(route('teacher')))->getTargetUrl();
        $class_label = ClassTransaction::find($schedule->class_id)?->label() ?? 'Class';

        if ($recorded) {
            // What was stored, by student (not the current class list): students added later have no row.
            $records = DB::table('detail_absens as d')
                ->join('students as st', 'st.id', 'd.student_id')
                ->where('d.header_absen_id', $headerId)
                ->orderBy('st.LongName')
                ->get(['st.LongName as nama', 'st.nis', 'd.Description', 'd.Notes']);

            return view('teacher.absen', compact('schedule', 'recorded', 'records', 'class_label', 'return_url'));
        }

        $className = (string) DB::table('class_transactions as ct')
            ->join('class_types as t', 't.id', 'ct.class_type_id')
            ->where('ct.id', $schedule->class_id)
            ->value('t.class_name');
        // Same query and order as the head page (staff AttendanceController::edit), so row indexes match.
        $students = DB::table('mapping_class_children')
            ->join('students', 'students.id', 'mapping_class_children.student_id')
            ->selectRaw('students.id as id, students.nis as nis, students.LongName as nama, students.Quota, students.MaxQuota')
            ->where('mapping_class_children.class_id', $schedule->class_id)
            ->where('students.Status', 'aktif')
            ->get();
        // Nothing is recorded yet, so the gate applies to every student (the save refuses the same rows).
        $mustPay = $students->filter(fn ($s) => AttendancePaymentGate::requiresPayment($s, $schedule->date, $className))
            ->pluck('id')->flip();
        $details = collect();

        return view('teacher.absen', compact('schedule', 'recorded', 'students', 'details', 'mustPay', 'class_label', 'return_url'));
    }

    public function getAbsen(RecordAttendanceRequest $req, Schedule $schedule)
    {
        if (HeaderAbsen::where('schedules_id', $schedule->id)->exists()) {
            return $this->backTo($req->return_url)->with('error', 'Jadwal ini sudah diabsen, jadi tidak bisa diubah lagi.');
        }

        // Checked again on save: a form opened in time may be submitted after the window closed.
        if (! AttendanceWindow::isOpen($schedule->date)) {
            return $this->backTo($req->return_url)->with('error', self::NOT_OPEN);
        }

        $rows = AttendanceRecorder::rowsFromRequest($req->validated());
        $saved = AttendanceRecorder::record($schedule, $rows, Auth::id(), allowEdit: false);

        return $this->backTo($req->return_url)->with('msg', AttendanceRecorder::message('Success Making Attendance', count($rows), $saved));
    }

    private function withStatus(object $session, CarbonImmutable $now): object
    {
        $session->status = AttendanceWindow::status($session->date, $session->recorded, $now);

        return $session;
    }

    /**
     * The teacher's sessions on one Jakarta date (Y-m-d) in active classes, by time.
     * The keyword matches the course name or the name of a student in the class.
     */
    private function sessionsOn(int $teacherId, string $date, ?string $keyword): Collection
    {
        $activeStudents = DB::table('mapping_class_children as c')
            ->join('students as st', 'st.id', 'c.student_id')
            ->whereColumn('c.class_id', 's.class_id')
            ->where('st.Status', 'aktif')
            ->selectRaw('COUNT(*)');

        return DB::table('schedules as s')
            ->join('class_transactions as ct', 'ct.id', 's.class_id')
            ->leftJoin('class_types as t', 't.id', 'ct.class_type_id')
            ->leftJoin('header_absens as h', 'h.schedules_id', 's.id')
            // whereIn, not a join: a class mapped to two teachers must not list its sessions twice.
            ->whereIn('s.class_id', DB::table('mapping_class_teachers')->where('user_id', $teacherId)->select('class_id'))
            ->where('ct.Status', 'aktif')
            ->whereDate('s.date', $date)
            ->when(filled($keyword), fn ($query) => $query->where(fn ($match) => $match
                ->where('t.class_name', 'like', Like::contains($keyword))
                ->orWhereIn('s.class_id', DB::table('mapping_class_children as c')
                    ->join('students as st', 'st.id', 'c.student_id')
                    ->where('st.LongName', 'like', Like::contains($keyword))
                    ->select('c.class_id'))))
            ->select('s.id', 's.date', 's.class_id', 't.class_name')
            ->selectRaw('h.id IS NOT NULL as recorded')
            ->selectSub($activeStudents, 'students')
            ->orderBy('s.date')
            ->orderBy('s.id')
            ->get()
            ->map(function (object $session) {
                $session->recorded = (bool) $session->recorded;
                $session->students = (int) $session->students;

                return $session;
            });
    }
}
