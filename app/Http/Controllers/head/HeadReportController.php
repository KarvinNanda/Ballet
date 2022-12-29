<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HeadReportController extends Controller
{
    public function index(){
        $data = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                class_transactions.ClassName as class_name,
                header_absens.id as header_id
            ')
            ->orderBy('schedules.date')
            ->get();
        return view('head.report.index',compact('data'));
    }

    public function print(HeaderAbsen $header){
        $find = HeaderAbsen::find($header->id);

        $report = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('detail_absens','detail_absens.header_absen_id','header_absens.id')
            ->join('students','detail_absens.student_id','students.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->selectRaw('
                schedules.date as date,
                detail_absens.Description as description,
                students.LongName as student_name,
                class_transactions.ClassName as class_name
            ')
            ->where('schedules.date','=',Carbon::parse($find->Schedules->date)->toDateString())
            ->orderBy('schedules.date')
            ->get();
        $pdf = Pdf::loadView('head.report.print',compact('report'))
            ->download('Laporan Siswa '.Carbon::parse($find->Schedules->date)->toDateString().'.pdf');
        return $pdf;
    }

}
