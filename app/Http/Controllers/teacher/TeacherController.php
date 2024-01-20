<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
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
//        dd(Carbon::parse('2023-02-10')->diffInDays('2024-01-20'));

        $data = ClassTransaction::leftjoin('schedules','class_transactions.id','schedules.class_id')
        ->leftjoin('mapping_class_children','class_transactions.id','mapping_class_children.class_id')
        ->selectRaw('
            schedules.date,
            class_transactions.id as id,
            class_transactions.class_type_id,
            schedules.id as schedule_id,
            COUNT(student_id) as people_count
        ')
        ->whereDate('schedules.date','<=',now()->toDateString())
        ->orderBy('schedules.date','desc')
        ->groupBy('schedules.date')
        ->paginate(5);

//        foreach ($data as $d){
//            echo now()->diffInDays(Carbon::parse($d->date))." ".Carbon::parse($d->date)." ".now()."<br>";
//        }

        return view('teacher.index',compact('data'));
    }

    public function viewAbsen($id){
        $view = Schedule::find($id);
        $class_name = DB::table('class_transactions')
            ->leftJoin('schedules','class_transactions.id','schedules.class_id')
            ->leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('schedules.id',$view->id)
            ->first()->class_name;

        $class = DB::table('mapping_class_children')
            ->leftjoin('students','students.id','mapping_class_children.student_id')
            ->selectRaw('
                students.id as id,
                students.nis as nis,
                students.LongName as nama,
                students.Quota
            ')
            ->where('mapping_class_children.class_id','=',$view->class_id)
            ->where('students.Status','!=','trial')
            ->get();
        $header = DB::table('header_absens')->where('schedules_id',"=",$id)->first();
        if(!is_null($header)){
            $detail = DB::table('detail_absens')->where('header_absen_id',$header->id)->get();
            return view('teacher.absen',compact("view","class","detail","class_name"));
        }

        return view('teacher.absen',compact("view","class","class_name"));


    }

    public function getAbsen(Request $req, Schedule $schedule){
        $header = new HeaderAbsen();
        $header->schedules_id = $schedule->id;
        $header->teacher_id = Auth::user()->id;
        $header->save();

//        $class_name = DB::table('class_transactions')
//            ->leftJoin('schedules','class_transactions.id','schedules.class_id')
//            ->leftJoin('class_types','class_types.id','class_transactions.class_type_id')
//            ->where('schedules.id',$schedule->id)
//            ->first()->class_name;

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
            DB::table('students')->where('id',$students[$i]->id)->update(['Quota' => $students[$i]->Quota + 1]);
        }
        return redirect()->route("teacher")->with('msg','Success Making Attendance');
    }
}
