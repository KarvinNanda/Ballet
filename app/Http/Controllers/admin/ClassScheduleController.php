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

class ClassScheduleController extends Controller
{
    public function viewSchedule(Request $req){
        $classId = $req->classId;
        $class = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                class_transactions.ClassName as classname,
                schedules.date as date,
                schedules.time as time
            ')->where('schedules.class_id',$req->classId)
            ->simplePaginate(5);
        return view('admin.class.viewSchedule',compact('class','classId'));
    }

    public function viewaddScheduleClass(Request $req){
        $classId = $req->classId;
        $test = Schedule::find(1);
        return view('admin.class.viewaddSchedule',compact('classId'));
    }

    public function addSchedule(Request $req){
        $schedule = new Schedule();
        $date = Carbon::parse($req->dateTime);
        $schedule->class_id = $req->classId;
        $schedule->date = $date;
        $schedule->time = $date;
        $schedule->save();
//        return redirect()->back();
        return redirect()->route("viewaddScheduleClass");

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

    public function getAbsen(Request $req, Schedule $schedule){
//        dd($req);
        //header
        $header = new HeaderAbsen();
        $header->schedule_id = $schedule->id;
        $header->is_report = "false";
        $header->save();

        $header_id = DB::table("header_absens")->orderBy('id','desc')->first();
        //detail

        $students = Student::whereIn('nis',$req->nis)->get();
        for($i = 0;$i < count($req->nis);$i++){
            $detail = new DetailAbsen();
            if($req->check[$i] == "on"){
                $detail->header_absen_id = $header_id->id;
                $detail->student_id = $students[$i]->id;
                $detail->Description = "Masuk";
                $detail->save();
            }else{
                $detail->header_absen_id = $header_id->id;
                $detail->student_id = $students[$i]->id;
                $detail->Description = $req->keterangan[$i];
                $detail->save();
            }
        }
        return "ok";


    }



}
