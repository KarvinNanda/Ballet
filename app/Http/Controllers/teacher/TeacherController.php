<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\teacher\Concerns\AuthorizesTeacherClasses;
use App\Models\ClassTransaction;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    use AuthorizesTeacherClasses;

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
        ->where('mapping_class_teachers.user_id', $request->user()->id)
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
        ->groupBy('schedules.id')
        ->paginate(5);

//        foreach ($data as $d){
//            echo now()->diffInDays(Carbon::parse($d->date))." ".Carbon::parse($d->date)." ".now()."<br>";
//        }

        return view('teacher.index',compact('data'));
    }

    public function viewAbsen($id){
        $this->authorizeSchedule($id);
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

    public function getAbsen(Request $req, Schedule $schedule)
    {
        $this->authorizeClass($schedule->class_id);

        if (HeaderAbsen::where('schedules_id', $schedule->id)->exists()) {
            return $this->backTo($req->return_url)->with('error', 'Jadwal ini sudah diabsen, jadi tidak bisa diubah lagi.');
        }

        // Only students of this class, looked up by NIS so each form row keeps its own student.
        $students = Student::whereIn('nis', (array) $req->nis)
            ->whereIn('id', DB::table('mapping_class_children')->where('class_id', $schedule->class_id)->pluck('student_id'))
            ->get()
            ->keyBy('nis');

        DB::transaction(function () use ($req, $schedule, $students) {
            $header = new HeaderAbsen();
            $header->schedules_id = $schedule->id;
            $header->teacher_id = Auth::id();
            $header->save();

            foreach ((array) $req->nis as $i => $nis) {
                $student = $students->get($nis);
                if ($student === null) {
                    continue;
                }

                $note = $req->keterangan[$i] ?? 'Select...';
                $detail = new DetailAbsen();
                $detail->header_absen_id = $header->id;
                $detail->student_id = $student->id;
                $detail->Notes = $note === 'Permission' ? ($req->notes[$i] ?? '') : '';
                $detail->Description = ($req->check[$i] ?? 'off') === 'on' ? 'Attend' : ($note === 'Select...' ? 'Absent' : $note);
                $detail->save();

                DB::table('students')->where('id', $student->id)->increment('Quota');
            }
        });

        return $this->backTo($req->return_url)->with('msg', 'Success Making Attendance');
    }
}
