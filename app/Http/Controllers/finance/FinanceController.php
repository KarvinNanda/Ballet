<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $req){
        $search = $req->search;
        if(is_null($search)) $stocks = Stock::orderBy('id','desc')->paginate(5);
        else $stocks = Stock::where('name','like',"%$search%")->orderBy('id','desc')->paginate(5);
        return view('finance.index',compact('stocks'));
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
            )")
            ->groupby(['month','month_num'])
            ->orderBy('month_num')
            ->get();

            return view('finance.report.teacher.index',compact('data'));
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
                ct2.class_price,
                st.Quota as meet,
                case
                 when ct2.class_name = 'Intensive Class' then (ct2.class_price / 12)
                    when ct2.class_name = 'Intensive Kids' then (ct2.class_price / 12)
                    when ct2.class_name = 'Pointe Class' then (ct2.class_price / 4)
                    else (ct2.class_price / 8)
                end as paid,
                u.percent as teacher_reward
            ")
            ->whereBetween('s.date',[$first,$last])
            ->whereRaw("(
                (ct2.class_name like '%intensive%' and st.Quota > 0)
                or (ct2.class_name like '%Pointe%' and st.Quota > 0)
                or ((ct2.class_name not like '%pointe%' and ct2.class_name not like '%intensive%') and st.Quota > 5)
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
            ->orderBy('ct2.class_price')
            ->get()
            ->groupBy('teacherName');

        $pdf = Pdf::loadView('finance.report.teacher.print',compact('report','getmonth'))
            ->stream('Laporan Kehadiran Guru '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function reportStudent(Request $req){
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
            ->where(function ($query) use ($req) {
                if($req->status != null){
                    $query->where('transactions.payment_status',$req->status);
                }
                if($req->class != null){
                    $query->where('class_types.class_name',$req->class);
                }
            })
            ->orderBy('class','asc')
            ->get();


        $pdf = Pdf::loadView('finance.report.student.print',compact('report'))
            ->stream('Laporan Finansial Siswa Aktif '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function reportStudentPage(){
        $classes = ClassType::all();
        return view('finance.report.student.index',compact('classes'));
    }
}
