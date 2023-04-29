<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\HeaderAbsen;
use App\Models\Rekenings;
use App\Models\ReportStock;
use App\Models\Schedule;
use App\Models\Stock;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(){
//        $temp = now()->setTimezone('GMT+7')->lastOfMonth()->setMonth(1);
//        $temp2 = now()->setTimezone('GMT+7')->firstOfMonth()->setMonth(1);
//        dd($temp,$temp2);

        $stocks = Stock::simplePaginate(5);
        return view('finance.index',compact('stocks'));
    }

    public function reportTeacherPage(){
        $data = DB::table('schedules as s')
            ->join('header_absens as ha','ha.schedules_id','s.id')
            ->selectRaw('
                count(*) as id,
                monthname(date) as month,
                month(date) as month_num
            ')
            ->groupby(['month','month_num'])
            ->orderBy('month_num')
            ->get();

            return view('finance.report.teacher.index',compact('data'));
    }

    public function reportTeacher($month){
        $first = now()->setTimezone('GMT+7')->firstOfMonth()->setMonth($month);
        $last = now()->setTimezone('GMT+7')->lastOfMonth()->setMonth($month);
        $report = DB::table('header_absens as ha')
            ->join('schedules as s','s.id','ha.schedules_id')
            ->join('users as u','u.id','ha.teacher_id')
            ->join('mapping_class_children as mcc','s.class_id','mcc.class_id')
            ->join('transactions as t','mcc.student_id','t.students_id')
            ->join('class_transactions as ct','ct.id','s.class_id')
            ->join('class_types as ct2','ct.class_type_id','ct2.id')
            ->selectRaw('
                u.name,
                concat(u.percent,"%") as percentase,
                if(t.discount = 0,sum(t.price),sum(t.price * (t.discount /100))) as price,
                (select count(teacher_id) from header_absens ha where ha.teacher_id = u.id group by teacher_id) as attendence,
                if(t.discount=0,sum(t.price * (u.percent / 100)) + sum(t.price),sum(t.price * (t.discount /100)) + sum((t.price * (t.discount /100))) * (u.percent / 100)) as total,
                monthname(date) as month,
                (select count(mcc2.student_id) from mapping_class_children mcc2,mapping_class_teachers mct where mct.class_id = mcc2.class_id and mct.user_id=u.id)  as students,
                ct2.class_name
            ')
            ->groupBy('u.name')
            ->whereBetween('s.date',[$first,$last])
            ->distinct()
            ->get();

        $pdf = Pdf::loadView('finance.report.teacher.print',compact('report'))
            ->download('Laporan Kehadiran Guru '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function reportStudent(){
        $report = Transaction::join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->join('mapping_class_teachers','class_transactions.id','mapping_class_teachers.class_id')
            ->join('users','users.id','mapping_class_teachers.user_id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                students.LongName as name,
                class_types.class_name as class,
                users.name as teacher,
                transactions.discount,
                transactions.payment_status as status,
                transactions.price as price
            ')
            ->where('students.Status','aktif')
            ->get();


        $pdf = Pdf::loadView('finance.report.student.print',compact('report'))
            ->download('Laporan Finansial Siswa Aktif '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }
}
