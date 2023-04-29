<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\HeaderAbsen;
use App\Models\ReportStock;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HeadReportController extends Controller
{
    public function classAttendence(){
        $data = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                class_types.class_name as class_name,
                header_absens.id as header_id
            ')
            ->orderBy('schedules.date')
            ->groupBy('class_name')
            ->get();
        return view('head.report.class-attendence.index',compact('data'));
    }

    public function printClassAttendence(HeaderAbsen $header,$className){
        $find = HeaderAbsen::find($header->id);

        $firstDayOfClass = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->where('class_types.class_name','like',"%$className%")
            ->orderBy('schedules.date')
            ->first();

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
            ->where('class_types.class_name',$className)
            ->orderBy('schedules.date')
            ->get()
            ->groupBy('schedules.date');

        $pdf = Pdf::loadView('admin.report.class-attendence.print',compact('report','className'))
            ->download('Laporan Absensi Siswa Kelas '.$className.' '.Carbon::now()->setTimezone("GMT+7")->format('dmY').'.pdf');
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

        $pdf = Pdf::loadView('head.report.active-student.print',compact('report'))
            ->setPaper('a3', 'landscape')
            ->download('Laporan Siswa Aktif '.now()->setTimezone('GMT+7')->format('dmY').'.pdf');
        return $pdf;
    }

    public function stock(){
        $data = ReportStock::all()->groupBy('report_date');
        return view('head.report.stock.index',compact('data'));
    }

    public function printStock(ReportStock $report_stock){
        $report = ReportStock::whereDate('report_date',$report_stock->report_date)
                ->get()
            ->groupBy('stock_id');
        $date = $report_stock->report_date;
        $in=$out=0;
        foreach($report as $r){
            foreach($r as $i){
                $in+=$i->in;
                $out+=$i->out;
            }
            Stock::where('name',$r[0]->stock->name)->update(['quantity' => $r[0]->stock->quantity + ($in-$out)]);
            $in=$out=0;
        }

        $pdf = Pdf::loadView('head.report.stock.print',compact('report','date'))
            ->download('Laporan Stock '.Carbon::parse($report_stock->report_date)->format('dmY').'.pdf');
        return $pdf;
    }

}
