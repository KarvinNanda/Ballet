<?php

namespace App\Http\Controllers\teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(){
        $data = DB::table('class_transactions')
            ->join('schedules','class_transactions.id','schedules.class_id')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
            ->selectRaw('
                class_transactions.ClassName as class,
                schedules.date
            ')
            ->where('mapping_class_teachers.user_id',Auth::user()->id)
            ->orderBy('schedules.date')
            ->get();
        return view('teacher.index',compact('data'));
    }
}
