<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherClassController extends Controller
{
    public function index()
    {
        $data = DB::table('class_transactions')
            ->join('mapping_class_teachers', 'mapping_class_teachers.class_id', 'class_transactions.id')
            ->join('mapping_class_children', 'mapping_class_children.class_id', 'class_transactions.id')
            ->join('class_types', 'class_transactions.class_type_id', 'class_types.id')
            ->selectRaw('
                class_transactions.id as id,
                class_types.class_name as class,
                COUNT(mapping_class_children.class_id) as students
            ')
            ->where('mapping_class_teachers.user_id', Auth::user()->id)
            ->groupBy(['class'])
            ->get()
            ->groupBy('class');
        return view('teacher.class.index', compact('data'));
    }

    public function viewDetail(Request $request)
    {
        $data = DB::table('class_transactions')
            ->join('mapping_class_children', 'mapping_class_children.class_id', 'class_transactions.id')
            ->join('students', 'mapping_class_children.student_id', 'students.id')
            ->selectRaw('
                students.LongName as student_name,
                YEAR(CURDATE()) - YEAR(students.dob) as student_old,
                students.dob as student_dob
            ')
            ->where('mapping_class_children.class_id', $request->id)
            ->get();
        return view('teacher.class.detail', compact('data'));
    }

    public function viewSchedule(Request $req, $id)
    {
        $classId = $id;
        $class = DB::table('schedules')
            ->join('class_transactions', 'class_transactions.id', 'schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                schedules.id as id
            ')->where('schedules.class_id', $classId)->orderBy("date")
            ->get();

        return view('teacher.class.viewSchedule', compact('class', 'classId'));
    }

    public function deleteScheduleClass($id, $classId)
    {
        $classDelete = DB::table('schedules')->where('schedules.id', $id)->where('class_id', $classId);
        $classDelete->delete();
        return redirect()->route("viewAllScheduleTeacher", ['userId' => auth()->id()]);
    }

    public function viewUpdateScheduleClass(Request $req)
    {
        $schedule = Schedule::find($req->scheduleId);
        return view('teacher.class.viewUpdateSchedule', compact('schedule'));
    }

    public function updateSchedule(Request $req)
    {
        $schedule = Schedule::find($req->scheduleId);
        $schedule->date = Carbon::parse($req->dateTime);
        $schedule->save();
        return redirect()->route("viewAllScheduleTeacher", ['userId' => auth()->id()]);
    }

    public function viewaddScheduleClass(Request $req, $id)
    {
        $classId = $id;
        $test = Schedule::find($classId);

        return view('teacher.class.viewaddSchedule', compact('classId'));
    }

    public function viewAddMultipleScheduleClass(Request $req, $id)
    {
        $classId = $id;
        $test = Schedule::find($classId);
        return view('teacher.class.addMultipleSchedule', compact('classId'));
    }

    public function addSchedule(Request $req, $id)
    {
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
            $schedule->class_id = $req->classId;
            $schedule->date = $date;
            $schedule->save();
        }

        return redirect()->route("viewScheduleClassTeacher", ['id' => $id]);
    }

    public function addMultipleSchedule(Request $req)
    {
        $date = Carbon::parse($req->dateTime);

        for ($i = 0; $i < $req->ScheduleLoop; $i++) {
            $schedule = new Schedule();
            $schedule->class_id = $req->classId;
            $schedule->date = $date;
            $schedule->save();
            $date->addDay(7);
        }

        return redirect()->route("viewScheduleClassTeacher", ['id' => $req->classId]);
    }

    public function viewClassSchedule(Request $request, $id)
    {
        $userId = $id;
        $classes = ClassTransaction::whereHas('mapping', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('Status', 'aktif')->simplePaginate(5);

        return view('teacher.class.schedule', compact('classes'));
    }
}
