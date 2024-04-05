<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use App\Models\HeaderAbsen;
use App\Models\ReportStock;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeadReportController extends Controller
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
        return view('head.report.class-attendence.index',compact('data'));
    }

    public function printClassAttendence($header,$teacher){

        // ini_set('max_execution_time',3600);
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

        $pdf = Pdf::loadView('head.report.class-attendence.print',compact('report','className','first','teacher'))
            ->setPaper('a4', 'landscape')
            ->stream('Laporan Absensi Siswa Kelas '.$className.' '.Carbon::now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function printActiveStudent(Request $req){
        ini_set('max_execution_time',3600);
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
        return view('head.report.active-student.index',compact('classes'));
    }

    public function stock(){
        $data = ReportStock::all()->groupBy('report_date');
        return view('head.report.stock.index',compact('data'));
    }

    public function printStock(Request $req){
        // ini_set('max_execution_time',3600);
        $start_date = $req->start_date." 00:00:00";
        $end_date = $req->end_date." 23:59:59";
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

        $pdf = Pdf::loadView('head.report.stock.print',compact('report','start_date','end_date','stock'))
            ->stream('Laporan Stock '.$date.'.pdf');
        return $pdf;
    }

    public function reportTeacherPage(){
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

            return view('head.report.teacher.index',compact('data'));
    }

    public function reportTeacher($month){
        $first = now()->setTimezone('GMT+7')->firstOfMonth()->setMonth($month);
        $last = now()->setTimezone('GMT+7')->lastOfMonth()->setMonth($month);
        $getmonth = DB::select(DB::raw("select monthname('$first') as month"));

        $report = DB::table('header_absens as ha')
            ->join('schedules as s','s.id','ha.schedules_id')
            ->join('users as u','u.id','ha.teacher_id')
            ->join('mapping_class_children as mcc','s.class_id','mcc.class_id')
            ->join('students as st','st.id','mcc.student_id')
            ->join('transactions as t','mcc.student_id','t.students_id')
            ->join('class_transactions as ct','ct.id','s.class_id')
            ->join('class_types as ct2','ct.class_type_id','ct2.id')
            ->selectRaw("
                st.LongName as studentName,
                u.name as teacherName,
                ct2.class_name,
                ct.price,
                st.Quota as meet,
                case
                 when ct2.class_name = 'Intensive Class' then (ct.price / 12)
                    when ct2.class_name = 'Intensive Kids' then (ct.price / 12)
                    when ct2.class_name = 'Pointe Class' then (ct.price / 4)
                    else (ct.price / 8)
                end as paid,
                u.percent as teacher_reward
            ")
            ->whereBetween('s.date',[$first,$last])
            ->whereRaw("(
                (ct2.class_name like '%intensive%' and st.Quota > 0)
                or (ct2.class_name like '%Pointe%' and st.Quota > 0)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.Quota > 5)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.is_new = 1)
            )")
            ->where(function($q) use ($month){
                if($month + 1 > 12){
                    $q->whereRaw("month(t.transaction_date) = $month");
                    $q->whereRaw("month(t.transaction_date) = 1");
                } else {
                    $q->whereRaw("month(t.transaction_date) between $month and ".($month+1));
                }
            })
            ->distinct()
            ->orderBy('ct.price')
            ->get()
            ->groupBy('teacherName');

        $pdf = Pdf::loadView('head.report.teacher.print',compact('report','getmonth'))
            ->stream('Laporan Kehadiran Guru '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

}
