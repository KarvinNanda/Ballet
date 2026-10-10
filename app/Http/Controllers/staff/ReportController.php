<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\ClassType;
use App\Models\ReportStock;
use App\Models\Stock;
use App\Http\Requests\DateRangeRequest;
use App\Http\Requests\Staff\ActiveStudentReportRequest;
use App\Support\TeacherReportQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function classAttendance(){
        $data = DB::table('schedules')
            ->join('header_absens','header_absens.schedules_id','schedules.id')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','schedules.class_id')
            ->join('users','mapping_class_teachers.user_id','users.id')
            ->selectRaw('
                class_types.class_name as class_name,
                class_transactions.id as class_id,
                users.name as teacher,
                (select count(*) from mapping_class_children where mapping_class_children.class_id = class_transactions.id) as students
            ')
            ->orderBy('schedules.date')
            ->groupBy('class_transactions.id')
            ->get();
        return view('staff.report.class-attendence.index',compact('data'));
    }

    public function printClassAttendance($header,$teacher){

        $firstDayOfClass = DB::table('schedules')
            ->join('class_transactions','class_transactions.id','schedules.class_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->where('class_transactions.id','=',$header)
            ->orderBy('schedules.date')
            ->first();

        // A class without schedules has nothing to report: return an empty report instead of failing on null.
        if ($firstDayOfClass === null) {
            $className = ClassTransaction::with("Type")->find($header)?->Type?->class_name ?? '';
            $report = collect();
            $first = collect();
            return Pdf::loadView('staff.report.class-attendence.print', compact('report','className','first','teacher'))
                ->setPaper('a4', 'landscape')
                ->stream('Laporan Absensi Siswa Kelas '.$className.' '.Carbon::now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        }

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
                students.id as student_id,
                students.LongName as student_name,
                class_types.class_name as class_name
            ')
            ->whereBetween('schedules.date',[$firstDayOfClass->date,$lastDayOfClass])
            ->where('class_transactions.id',$header)
            ->orderBy('schedules.date')
            ->get()
            ->groupBy('date');
        // One row per student recorded on ANY session of the period (not only the first one, so late joiners are listed).
        $first = $report->flatten(1)->whereNotNull('student_id')->unique('student_id')->sortBy('student_name')->values();

        $pdf = Pdf::loadView('staff.report.class-attendence.print',compact('report','className','first','teacher'))
            ->setPaper('a4', 'landscape')
            ->stream('Laporan Absensi Siswa Kelas '.$className.' '.Carbon::now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function printActiveStudent(ActiveStudentReportRequest $req){
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
                if($req->validated('class') != null){
                    $query->where('class_types.class_name',$req->validated('class'));
                }
            })
            ->orderBy('class_name')
            ->get();

        $pdf = Pdf::loadView('staff.report.active-student.print',compact('report'))
            ->setPaper('a3', 'landscape')
            ->stream('Laporan Siswa Aktif '.now()->setTimezone('GMT+7')->format('dmY').'.pdf');
        return $pdf;
    }

    public function activeStudent(){
        $classes = ClassType::orderBy('class_name')->get();
        return view('staff.report.active-student.index',compact('classes'));
    }

    public function stock(){
        return view('staff.report.stock.index');
    }

    public function printStock(DateRangeRequest $req){
        $start_date = $req->validated('start_date')." 00:00:00";
        $end_date = $req->validated('end_date')." 23:59:59";
        $report = ReportStock::whereBetween('report_date',[$start_date,$end_date])
            // ->groupBy('stock_id')
            ->selectRaw("
                stock_id,
                first_qty,
                report_date,
                sum(report_stocks.in) as in_qty,
                sum(report_stocks.out) as out_qty
            ")
            ->groupBy('stock_id','first_qty','report_date')
            ->get();
            
        $date = Carbon::parse($start_date)->toDateString() == Carbon::parse($end_date)->toDateString() ? Carbon::parse($start_date)->format('dmY') : Carbon::parse($start_date)->format('dmY')."-".Carbon::parse($end_date)->format('dmY');

        $stock = Stock::all();

        $pdf = Pdf::loadView('staff.report.stock.print',compact('report','start_date','end_date','stock'))
            ->stream('Laporan Stock '.$date.'.pdf');
        return $pdf;
    }

    public function teacher(){
        $data = DB::table('schedules as s')
            ->join('header_absens as ha','ha.schedules_id','s.id')
            ->join('class_transactions as ct','ct.id','s.class_id')
            ->join('class_types as ct2','ct.class_type_id','ct2.id')
            ->join('mapping_class_children as mcc','s.class_id','mcc.class_id')
            ->join('students as st','st.id','mcc.student_id')
            ->selectRaw('
                count(*) as id,
                monthname(date) as month,
                month(date) as month_num
            ')
            ->whereRaw("(
                (ct2.class_name like '%intensive%' and st.Quota > 0)
                or (ct2.class_name like '%Pointe%' and st.Quota > 0)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.Quota > 5)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.is_new = 1)
            )")
            ->groupby(['month','month_num'])
            ->orderBy('month_num')
            ->get();

            return view('staff.report.teacher.index',compact('data'));
    }

    public function printTeacher($month){
        $month = (int) $month;
        abort_unless($month >= 1 && $month <= 12, 404);

        $first = now()->setTimezone('GMT+7')->setDate(now()->setTimezone('GMT+7')->year, $month, 1)->startOfDay();
        $getmonth = [(object) ['month' => $first->format('F')]];
        $report = TeacherReportQuery::rows($month);

        $pdf = Pdf::loadView('staff.report.teacher.print',compact('report','getmonth'))
            ->stream('Laporan Kehadiran Guru '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

}
