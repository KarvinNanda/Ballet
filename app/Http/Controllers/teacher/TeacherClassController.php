<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\teacher\Concerns\AuthorizesTeacherClasses;
use App\Http\Requests\Teacher\AddMultipleScheduleRequest;
use App\Http\Requests\Teacher\AddScheduleRequest;
use App\Http\Requests\Teacher\UpdateScheduleRequest;
use App\Models\ClassTransaction;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Support\AttendanceWindow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherClassController extends Controller
{
    use AuthorizesTeacherClasses;

    private const STARTED_MESSAGE = 'This session has already started; ask the head to change it.';

    public function index()
    {
        $classes = $this->classRows((int) Auth::id());

        return view('teacher.class.index', compact('classes'));
    }

    public function viewDetail(Request $request, $id)
    {
        // The route id only: the form posts no body id.
        $this->authorizeClass($id);
        $course = $this->courseName($id);
        // Same students and max quota source as the staff class detail (mapping_class_children.quota).
        $students = DB::table('mapping_class_children as c')
            ->join('students as st', 'st.id', 'c.student_id')
            ->where('c.class_id', $id)
            ->where('st.Status', '!=', 'non-aktif')
            ->orderBy('st.LongName')
            ->get(['st.LongName as name', 'st.Dob as dob', 'st.Quota as quota', 'c.quota as max_quota', 'st.Status as status']);

        return view('teacher.class.detail', compact('students', 'course'));
    }

    public function viewSchedule(Request $req, $id)
    {
        $this->authorizeClass($id);
        $classId = (int) $id;
        $course = $this->courseName($classId);
        $now = AttendanceWindow::now();

        $schedules = DB::table('schedules as s')
            ->leftJoin('header_absens as h', 'h.schedules_id', 's.id')
            ->where('s.class_id', $classId)
            ->whereNotNull('s.date')
            ->select('s.id', 's.date')
            ->selectRaw('h.id IS NOT NULL as recorded')
            ->orderBy('s.date', 'desc')
            ->orderBy('s.id', 'desc')
            ->get()
            ->map(function (object $session) use ($now) {
                $session->recorded = (bool) $session->recorded;
                $session->status = AttendanceWindow::status($session->date, $session->recorded, $now);

                return $session;
            });

        return view('teacher.class.viewSchedule', compact('schedules', 'classId', 'course'));
    }

    public function deleteScheduleClass($id, $classId)
    {
        $this->authorizeClass($classId);
        $schedule = Schedule::where('id', $id)->where('class_id', $classId)->firstOrFail();

        if (HeaderAbsen::where('schedules_id', $schedule->id)->exists()) {
            return redirect()->back()->with('error', 'Jadwal ini sudah diabsen, jadi tidak bisa diubah lagi.');
        }

        $schedule->delete();
        return redirect()->route("viewScheduleClassTeacher", ['id' => $classId])->with('msg','Success Delete Schedule');
    }

    public function viewUpdateScheduleClass(Request $req)
    {
        // Opened directly (no schedule posted from the schedule list): nothing to edit.
        if (! filter_var($req->query('scheduleId'), FILTER_VALIDATE_INT)) {
            return redirect()->route('viewClass');
        }

        $schedule = $this->authorizeSchedule($req->scheduleId);

        if (HeaderAbsen::where('schedules_id', $schedule->id)->exists()) {
            return redirect()->back()->with('error', 'Jadwal ini sudah diabsen, jadi tidak bisa diubah lagi.');
        }

        if ($this->hasStarted($schedule)) {
            return redirect()->back()->with('error', self::STARTED_MESSAGE);
        }

        $course = $this->courseName($schedule->class_id);

        return view('teacher.class.viewUpdateSchedule', compact('schedule', 'course'));
    }

    public function updateSchedule(UpdateScheduleRequest $req)
    {
        $schedule = $this->authorizeSchedule($req->integer('scheduleId'));

        if (HeaderAbsen::where('schedules_id', $schedule->id)->exists()) {
            return redirect()->back()->with('error', 'Jadwal ini sudah diabsen, jadi tidak bisa diubah lagi.');
        }

        if ($this->hasStarted($schedule)) {
            return redirect()->back()->with('error', self::STARTED_MESSAGE);
        }

        $schedule->date = Carbon::parse($req->dateTime);
        $schedule->save();
        return redirect()->route("viewScheduleClassTeacher", ['id' => $schedule->class_id])->with('msg','Success Update Schedule');
    }

    public function viewaddScheduleClass(Request $req, $id)
    {
        $this->authorizeClass($id);
        $classId = (int) $id;
        $course = $this->courseName($classId);

        return view('teacher.class.viewaddSchedule', compact('classId', 'course'));
    }

    public function viewAddMultipleScheduleClass(Request $req, $id)
    {
        $this->authorizeClass($id);
        $classId = (int) $id;
        $course = $this->courseName($classId);

        return view('teacher.class.addMultipleSchedule', compact('classId', 'course'));
    }

    public function addSchedule(AddScheduleRequest $req, $id)
    {
        $this->authorizeClass($id);
        $date = Carbon::parse($req->dateTime);
        $class_schedule = Schedule::where('class_id', $id)->get();
        $bool = true;
        foreach ($class_schedule as $sche) {
            if (Carbon::parse($sche->date)->toDateString() == Carbon::parse($date)->toDateString()) {
                $hour = Carbon::parse($date)->diff(Carbon::parse($sche->date))->format('%H');
                $num = (int)$hour;
                if ($num < 1) {
                    $bool = false;
                }
            }
        }

        if ($bool == false) {
            return redirect()->route("viewScheduleClassTeacher", ['id' => $id]);
        } else {
            $schedule = new Schedule();
            $schedule->class_id = $id; // the authorized route id, never a classId from the form body
            $schedule->date = $date;
            $schedule->save();
        }

        return redirect()->route("viewScheduleClassTeacher", ['id' => $id])->with('msg','Success Create Schedule');
    }

    public function addMultipleSchedule(AddMultipleScheduleRequest $req)
    {
        $this->authorizeClass($req->integer('classId'));
        $date = Carbon::parse($req->dateTime);

        for ($i = 0; $i < $req->ScheduleLoop; $i++) {
            $schedule = new Schedule();
            $schedule->class_id = $req->classId;
            $schedule->date = $date;
            $schedule->save();
            $date->addDay(7);
        }

        return redirect()->route("viewScheduleClassTeacher", ['id' => $req->classId])->with('msg','Success Create Schedule');
    }

    public function viewClassSchedule(Request $request, $id)
    {
        $this->authorizeSelf($id);
        $classes = $this->classRows((int) $id);

        return view('teacher.class.schedule', compact('classes'));
    }

    /** The teacher's active classes with their active student count (My classes and Schedules share it). */
    private function classRows(int $teacherId): Collection
    {
        $activeStudents = DB::table('mapping_class_children as c')
            ->join('students as st', 'st.id', 'c.student_id')
            ->whereColumn('c.class_id', 'ct.id')
            ->where('st.Status', 'aktif')
            ->selectRaw('COUNT(*)');

        return DB::table('class_transactions as ct')
            ->leftJoin('class_types as t', 't.id', 'ct.class_type_id')
            ->whereIn('ct.id', DB::table('mapping_class_teachers')->where('user_id', $teacherId)->select('class_id'))
            ->where('ct.Status', 'aktif')
            ->select('ct.id', 't.class_name')
            ->selectSub($activeStudents, 'students')
            ->orderBy('t.id')
            ->orderBy('ct.id')
            ->get();
    }

    /** Moving a session that has started (Open or Missed) would reopen the attendance window, so only the head may do it. */
    private function hasStarted(Schedule $schedule): bool
    {
        return AttendanceWindow::status($schedule->date, false) !== AttendanceWindow::NOT_STARTED;
    }

    private function courseName(mixed $classId): string
    {
        return ClassTransaction::find($classId)?->Type?->class_name ?? 'Class';
    }
}
