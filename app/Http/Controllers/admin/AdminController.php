<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
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
        return view('admin.index',compact('data'));
    }

    public function indexType(){
        $types = ClassType::all();
        return view('admin.class.classType',compact('types'));
    }

    public function viewUpdateType(Request $req){
        $type = ClassType::find($req->typeID);
        return view('admin.class.classTypeUpdate',compact('type'));
    }

    public function updateType(Request $req){
        $type = ClassType::find($req->typeID);
        $type->class_price = $req->inputPrice;
        $type->save();
        return redirect()->route('adminClassTypePage')->with('msg','Success Update Data Course');
    }
}
