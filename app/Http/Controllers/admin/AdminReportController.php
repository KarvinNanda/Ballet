<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use App\Models\HeaderAbsen;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function classAttendence(){
        $data = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','schedules.class_id')
            ->join('users','mapping_class_teachers.user_id','users.id')
            ->selectRaw('
                class_types.class_name as class_name,
                class_transactions.id as class_id,
                users.name as teacher
            ')
            ->orderBy('schedules.date')
            ->groupBy('class_transactions.id')
            ->get();
        return view('admin.report.class-attendence.index',compact('data'));
    }

    public function printClassAttendence($header,$teacher){
        $firstDayOfClass = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->where('class_transactions.id','=',$header)
            ->orderBy('schedules.date')
            ->first();

        $className = $firstDayOfClass->class_name;

        $lastDayOfClass = Carbon::parse($firstDayOfClass->date)->addMonth(5)->lastOfMonth();

        $report = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->leftjoin('detail_absens','detail_absens.header_absen_id','header_absens.id')
            ->leftjoin('students','detail_absens.student_id','students.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                schedules.date as date,
                detail_absens.Description as description,
                detail_absens.Notes as note,
                students.LongName as student_name,
                class_types.class_name as class_name
            ')
            ->whereBetween('schedules.date',[$firstDayOfClass->date,$lastDayOfClass])
            ->where('class_transactions.id',$header)
            ->orderBy('schedules.date')
            ->get()
            ->groupBy('date');
//        dd($report,$firstDayOfClass,$lastDayOfClass);
        foreach($report as $items){
            $first=$items;
            break;
        }

        $pdf = Pdf::loadView('admin.report.class-attendence.print',compact('report','className','first','teacher'))
            ->setPaper('a4', 'landscape')
            ->stream('Laporan Absensi Siswa Kelas '.$className.' '.Carbon::now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function printActiveStudent(Request $req){
        $report = DB::table('students')
            ->join('rekenings','rekenings.bank_rek','students.bank_rek')
            ->join('mapping_class_children','mapping_class_children.student_id','students.id')
            ->join('class_transactions','class_transactions.id','mapping_class_children.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->join('banks','rekenings.banks_id','banks.id')
            ->selectRaw('
                students.nis,
                students.LongName,
                students.Dob,
                YEAR(CURDATE()) - YEAR(students.Dob) as old,
                students.Address,
                students.nama_orang_tua,
                students.City,
                students.kode_pos,
                students.Phone1,
                students.Phone2,
                students.Whatsapp,
                students.Instagram,
                students.Line,
                students.Email,
                students.bank_rek,
                rekenings.nama_pengirim,
                banks.bank_name,
                class_types.class_name
            ')
            ->where('students.Status','aktif')
            ->where(function ($query) use ($req) {
                if($req->class != null){
                    $query->where('class_types.class_name',$req->class);
                }
            })
            ->orderBy('class_name')
            ->get();

        $pdf = Pdf::loadView('admin.report.active-student.print',compact('report'))
            ->setPaper('a3', 'landscape')
            ->stream('Laporan Siswa Aktif '.now()->setTimezone('GMT+7')->format('dmY').'.pdf');
        return $pdf;
    }

    public function printActiveStudentPage(){
        $classes = ClassType::all();
        return view('admin.report.active-student.index',compact('classes'));
    }


}
