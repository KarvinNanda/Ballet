<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\ClassTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HeadController extends Controller
{
    public function index(){
        $data = DB::table('class_transactions')
            ->join('schedules','class_transactions.id','schedules.class_id')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
            ->join('users','mapping_class_teachers.teacher_id','users.id')
            ->selectRaw('
                class_transactions.ClassName as class,
                schedules.time,
                schedules.date,
                users.name as teacherName
            ')
            ->orderBy('schedules.date')
            ->get();
//        dd(Carbon::now()->toDateString());
        return view('head.index',compact('data'));
    }
}
