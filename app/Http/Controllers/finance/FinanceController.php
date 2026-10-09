<?php

namespace App\Http\Controllers\finance;

use App\Http\Requests\Finance\StudentReportRequest;
use App\Http\Requests\SearchRequest;
use App\Support\TeacherReportQuery;
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
    public function index(SearchRequest $req){
        $sort = 'asc';
        // SearchRequest has already dropped junk search values.
        $stocks = Stock::search($req->query('search'))->orderBy('id','desc')->paginate(5)->withQueryString();
        return view('finance.index',compact('stocks','sort'));
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

            return view('finance.report.teacher.index',compact('data'));
    }

    public function reportTeacher($month){
        $month = (int) $month;
        abort_unless($month >= 1 && $month <= 12, 404);

        $first = now()->setTimezone('GMT+7')->setDate(now()->setTimezone('GMT+7')->year, $month, 1)->startOfDay();
        $getmonth = [(object) ['month' => $first->format('F')]];
        $report = TeacherReportQuery::rows($month);

        $pdf = Pdf::loadView('finance.report.teacher.print',compact('report','getmonth'))
            ->stream('Laporan Kehadiran Guru '.now()->setTimezone("GMT+7")->format('dmY').'.pdf');
        return $pdf;
    }

    public function reportStudent(StudentReportRequest $req){
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
                if($req->validated('status') != null){
                    $query->where('transactions.payment_status',$req->validated('status'));
                }
                if($req->validated('class') != null){
                    $query->where('class_types.class_name',$req->validated('class'));
                }
            })
            ->distinct()
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
