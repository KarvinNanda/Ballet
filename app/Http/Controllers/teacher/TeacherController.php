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
    public function index(Request $request){
//        dd(Carbon::parse('2023-02-10')->diffInDays('2024-01-20'));
        $keyword = $request->query('keyword');

        $data = ClassTransaction::leftjoin('schedules','class_transactions.id','schedules.class_id')
        ->leftjoin('mapping_class_children','class_transactions.id','mapping_class_children.class_id')
        ->leftjoin('mapping_class_teachers','class_transactions.id','mapping_class_teachers.class_id')
        ->leftjoin('students','students.id','mapping_class_children.student_id')
        ->leftjoin('users','users.id','mapping_class_teachers.user_id')
        ->leftjoin('class_types','class_transactions.class_type_id','class_types.id')
        ->selectRaw('
            schedules.date,
            class_transactions.id as id,
            class_transactions.class_type_id,
            schedules.id as schedule_id,
            COUNT(student_id) as people_count
        ')
        ->where('class_transactions.Status','aktif')
        ->where(function ($query) use ($keyword) {
            $query->where('students.LongName',"LIKE","%$keyword%")
            ->orWhere('users.name',"LIKE","%$keyword%")
            ->orWhere('class_types.class_name',"LIKE","%$keyword%");
            // ->orWhere('students.Phone1',"LIKE","%$keyword%")
            // ->orWhere('students.Phone2',"LIKE","%$keyword%")
            // ->orWhere('students.bank_rek',"LIKE","%$keyword%")
            // ->orWhere('students.nama_orang_tua',"LIKE","%$keyword%")
            // ->orWhere('students.Address',"LIKE","%$keyword%")
            // ->orWhere('rekenings.nama_pengirim',"LIKE","%$keyword%")
            // ->orWhere('banks.bank_name',"LIKE","%$keyword%");
        })
        ->whereDate('schedules.date','=',now()->setTimezone("GMT+7")->toDateString())
        ->orderBy('schedules.date','desc')
        ->groupBy('schedules.date')
        ->paginate(5);

//        foreach ($data as $d){
//            echo now()->diffInDays(Carbon::parse($d->date))." ".Carbon::parse($d->date)." ".now()."<br>";
//        }

        return view('teacher.index',compact('data'));
    }

    public function viewAbsen($id){
        $return_url = url()->previous();
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
                students.Quota,
                students.MaxQuota
            ')
            ->where('mapping_class_children.class_id','=',$view->class_id)
            ->where('students.Status','=','aktif')
            ->get();
        $header = DB::table('header_absens')->where('schedules_id',"=",$id)->first();
        if(!is_null($header)){
            $detail = DB::table('detail_absens')->where('header_absen_id',$header->id)->get();
            return view('teacher.absen',compact("view","class","detail","class_name",'return_url'));
        }

        return view('teacher.absen',compact("view","class","class_name",'return_url'));


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
        return redirect()->to($req->return_url)->with('msg','Success Making Attendance');
    }
}
