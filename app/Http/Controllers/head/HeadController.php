<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\HeaderAbsen;
use Illuminate\Support\Facades\DB;

class HeadController extends Controller
{
    public function index(){
        $data = DB::table('class_transactions')
            ->join('schedules','class_transactions.id','schedules.class_id')
            ->join('mapping_class_teachers','mapping_class_teachers.class_id','class_transactions.id')
            ->join('users','mapping_class_teachers.user_id','users.id')
            ->join('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                class_types.class_name as class,
                schedules.date,
                users.name as teacherName,
                class_transactions.id as id
            ')
            ->orderBy('schedules.date','desc')
            ->paginate(5);
        return view('head.index',compact('data'));
    }
}
