<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderAbsen;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function classAttendence(){
        $data = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                class_transactions.class_name as class_name,
                header_absens.id as header_id
            ')
            ->orderBy('schedules.date')
            ->get();
        return view('admin.report.class-attendence.index',compact('data'));
    }

    public function printClassAttendence(HeaderAbsen $header,$className){
        $find = HeaderAbsen::find($header->id);

        $report = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('detail_absens','detail_absens.header_absen_id','header_absens.id')
            ->join('students','detail_absens.student_id','students.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                detail_absens.Description as description,
                detail_absens.Notes as note,
                students.LongName as student_name,
                class_transactions.ClassName as class_name
            ')
            ->where('schedules.date','=',Carbon::parse($find->Schedules->date)->toDateTime())
            ->where('class_transactions.ClassName',$className)
            ->orderBy('schedules.date')
            ->get();

        $pdf = Pdf::loadView('admin.report.class-attendence.print',compact('report'))
            ->download('Laporan Absensi Siswa Kelas '.$report[0]->class_name.' '.Carbon::parse($find->Schedules->date)->format('dmY').'.pdf');
        return $pdf;
    }

    public function printActiveStudent(){
        $report = DB::table('students')
            ->join('rekenings','rekenings.bank_rek','students.bank_rek')
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
                banks.bank_name
            ')
            ->where('Status','aktif')
            ->orderBy('LongName')
            ->get();

        $pdf = Pdf::loadView('admin.report.active-student.print',compact('report'))
            ->setPaper('a3', 'landscape')
            ->download('Laporan Siswa Aktif '.now()->setTimezone('GMT+7')->format('dmY').'.pdf');
        return $pdf;
    }


}
