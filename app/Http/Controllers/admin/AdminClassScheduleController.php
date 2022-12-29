<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminClassScheduleController extends Controller
{
    public function viewSchedule(Request $req){
        $classId = $req->classId;
        $class = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                class_transactions.ClassName as classname,
                schedules.date as date,
                schedules.id as id
            ')->where('schedules.class_id',$req->classId)
            ->get();
        return view('admin.class.viewSchedule',compact('class','classId'));
    }

    public function viewaddScheduleClass(Request $req){
        $classId = $req->classId;
        $test = Schedule::find($classId);
        return view('admin.class.viewaddSchedule',compact('classId'));
    }

    public function viewAddMultipleScheduleClass(Request $req){
        $classId = $req->classId;
        $test = Schedule::find($classId);
        return view('admin.class.addMultipleSchedule',compact('classId'));
    }

    public function addSchedule(Request $req){

        $schedule = new Schedule();
        $date = Carbon::parse($req->dateTime);
        $schedule->class_id = $req->classId;
        $schedule->date = $date;
        $schedule->save();
        $test = Schedule::find($req->classId);
        return redirect()->route("adminClassView");
    }

    public function addMultipleSchedule(Request $req){

        $date = Carbon::parse($req->dateTime);

        for($i =0;$i < $req->ScheduleLoop;$i++){
            $schedule = new Schedule();
            $schedule->class_id = $req->classId;
            $schedule->date = $date;
            $schedule->save();
            $date->addDay(7);
        }


//        return redirect()->back();
        return redirect()->route("adminClassView");
    }


    public function viewAbsen(){
        $view = Schedule::find(1);
        $class = DB::table('mapping_class_children')->join('students','students.id','mapping_class_children.student_id')
        ->selectRaw('
            students.nis as nis,
            students.LongName as nama
        ')->where('mapping_class_children.class_id','=',$view->class_id)
        ->get();
        return view('admin.class.absen',compact("view","class"));
    }



    public function deleteScheduleClass($id,$classId){
        $classDelete = DB::table('schedules')->where('schedules.id',$id)->where('class_id',$classId);
        $classDelete->delete();
        return redirect()->route("adminClassView");
    }

    public function deleteTeacher(){

    }
}
