<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(){
        $data = DB::table('class_transactions')
        ->join('schedules','class_transactions.id','schedules.class_id')
        ->join('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
        ->join('users','mapping_class_teachers.user_id','users.id')
        ->join('class_types','class_transactions.class_type_id','class_types.id')
        ->selectRaw('
            class_types.class_name as class,
            schedules.date,
            users.name as teacherName,
            class_transactions.id as id,
            schedules.id as schedule_id
        ')
        ->orderBy('schedules.date')
        ->get();
        return view('teacher.index',compact('data'));
    }

    public function viewAbsen($id){
        $view = Schedule::find($id);
        $class = DB::table('mapping_class_children')->join('students','students.id','mapping_class_children.student_id')
            ->selectRaw('
            students.nis as nis,
            students.LongName as nama
        ')->where('mapping_class_children.class_id','=',$view->class_id)
            ->get();
        $header = DB::table('header_absens')->where('schedules_id',"=",$id)->first();
        if(!is_null($header)){
            $detail = DB::table('detail_absens')->where('header_absen_id',$header->id)->get();
            return view('teacher.absen',compact("view","class","detail"));
        }
        return view('teacher.absen',compact("view","class"));


    }

    public function getAbsen(Request $req, Schedule $schedule){
        $header = new HeaderAbsen();
        $header->schedules_id = $schedule->id;
        $header->save();

        $header_id = DB::table("header_absens")->orderBy('id','desc')->first();

        $students = Student::whereIn('nis',$req->nis)->get();
        for($i = 0;$i < count($req->nis);$i++){
            $detail = new DetailAbsen();
            $detail->header_absen_id = $header_id->id;
            $detail->student_id = $students[$i]->id;
            $detail->Notes = $req->keterangan[$i] == "Permission" ? $req->notes[$i] : '';
            if($req->check[$i] == "on"){
                $detail->Description = "Attend";
            }else{
                if($req->keterangan[$i]=="Select...")
                {
                    $detail->Description = "Absent";
                }
                else{
                    $detail->Description = $req->keterangan[$i];
                }
            }
            $detail->save();
        }
        return redirect()->route("teacher");
    }
}
