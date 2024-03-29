<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\HeaderAbsen;
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
            ->leftJoin('mapping_class_children',function($q){
                $q->on('mapping_class_children.class_id','class_transactions.id')
                    ->where('mapping_class_children.student_id','!=',0);
            })
            ->join('class_types', 'class_transactions.class_type_id', 'class_types.id')
            ->selectRaw('
                class_transactions.id as id,
                class_types.class_name as class,
                COUNT(mapping_class_children.class_id) as students
            ')
            ->where('mapping_class_teachers.user_id', Auth::user()->id)
            ->orderBy('class_types.id')
            ->groupBy(['class'])
            ->get()
            ->groupBy('class');
        return view('teacher.class.index', compact('data'));
    }

    public function viewDetail(Request $request)
    {
        $get_class = DB::table('class_transactions as ct')
                        ->leftJoin('class_types as ct2','ct2.id','ct.class_type_id')
                        ->where('ct.id', $request->id)->first();

        $data = DB::table('class_transactions')
            ->join('mapping_class_children', 'mapping_class_children.class_id', 'class_transactions.id')
            ->join('students', 'mapping_class_children.student_id', 'students.id')
            ->leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->selectRaw('
                students.LongName as student_name,
                students.age as student_old,
                students.dob as student_dob
            ')
            ->where('class_types.class_name','=', "$get_class->class_name")
            ->get();
        return view('teacher.class.detail', compact('data'));
    }

    public function viewSchedule(Request $req, $id)
    {
        $classId = $id;
        $class = DB::table('schedules')
            // ->join('class_transactions', 'class_transactions.id', 'schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                schedules.id as id
            ')
            ->where('schedules.class_id', $classId)
            // ->whereNotIn('schedules.id',function($q){
            //     $q->select('schedules_id')
            //         ->from('header_absens');
            // })
            ->orderBy('schedules.date','desc')
            ->get();

        return view('teacher.class.viewSchedule', compact('class', 'classId'));
    }

    public function deleteScheduleClass($id, $classId)
    {
        $classDelete = DB::table('schedules')->where('schedules.id', $id)->where('class_id', $classId);
        $classDelete->delete();
        return redirect()->route("viewScheduleClassTeacher", ['id' => $classId])->with('msg','Success Delete Schedule');
    }

    public function viewUpdateScheduleClass(Request $req)
    {
        // dd($req->all());
        $schedule = Schedule::find($req->scheduleId);
        $header_check = HeaderAbsen::where('schedules_id',$req->scheduleId)->first();
        if(!is_null($header_check)) return redirect()->back()->with('msg','This Schedule Already get Attendence');
        return view('teacher.class.viewUpdateSchedule', compact('schedule'));
    }

    public function updateSchedule(Request $req)
    {
        $schedule = Schedule::find($req->scheduleId);
        $schedule->date = Carbon::parse($req->dateTime);
        $schedule->save();
        return redirect()->route("viewScheduleClassTeacher", ['id' => $schedule->class_id])->with('msg','Success Update Schedule');
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

        return redirect()->route("viewScheduleClassTeacher", ['id' => $id])->with('msg','Success Create Schedule');
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

        return redirect()->route("viewScheduleClassTeacher", ['id' => $req->classId])->with('msg','Success Create Schedule');
    }

    public function viewClassSchedule(Request $request, $id)
    {
        $userId = $id;

        $classes = ClassTransaction::select(
            'class_transactions.id',
            'class_name',
            'class_price',
            'Status',
            'class_type_id',
            'student_id',
            'class_id',
            DB::raw('COUNT(student_id) as people_count'))
            // ->whereHas('mapping', function ($query) use ($userId) {
            //     $query->where('user_id', $userId);
            // })
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->leftJoin('mapping_class_children',function($q){
                $q->on('mapping_class_children.class_id','class_transactions.id')
                    ->where('mapping_class_children.student_id','!=',0);
            })
            ->HavingRaw('COUNT(student_id) > 0')
            ->orderBy('class_types.id')
            ->groupBy('class_transactions.id')
            ->where('Status', 'aktif')
            ->paginate(20);

        return view('teacher.class.schedule', compact('classes'));
    }
}
