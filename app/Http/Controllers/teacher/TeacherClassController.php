<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherClassController extends Controller
{
    public function index(){
        $data = DB::table('class_transactions')
            ->join('schedules','class_transactions.id','schedules.class_id')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
            ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->selectRaw('
                class_transactions.ClassName as class,
                schedules.date,
                COUNT(mapping_class_children.class_id) as students
            ')
            ->where('mapping_class_teachers.user_id',Auth::user()->id)
            ->groupBy(['class','schedules.date'])
            ->orderBy('schedules.date')
            ->get();
        return view('teacher.class.index',compact('data'));
    }

    public function viewDetail(ClassTransaction $class){
        $data = DB::table('class_transactions')
            ->join('mapping_class_children','mapping_class_children.class_id','class_transactions.id')
            ->join('students','mapping_class_children.student_id','students.id')
            ->selectRaw('
                students.LongName as student_name,
                YEAR(CURDATE()) - YEAR(students.dob) as student_old,
                students.dob as student_dob
            ')
            ->where('mapping_class_children.class_id',$class->id)
            ->get();
        return view('teacher.class.detail',compact('data'));
    }
}
