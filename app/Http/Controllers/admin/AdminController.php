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
            ->whereDate('schedules.date','<=',now()->setTimezone("GMT+7")->addDay(7)->toDateString())
            ->where('class_transactions.Status','aktif')
            ->orderBy('schedules.date','desc')
            ->paginate(5);
        return view('admin.index',compact('data'));
    }

    public function indexType(){
        $types = ClassType::orderBy('id')->get();
        return view('admin.class.classType',compact('types'));
    }

    public function viewUpdateType(Request $req){
        $return_url = url()->previous();
        $type = ClassType::find($req->typeID);
        return view('admin.class.classTypeUpdate',compact('type','return_url'));
    }

    public function updateType(Request $req){
        $type = ClassType::find($req->typeID);
        $type->class_price = $req->inputPrice;
        $type->save();

        DB::table('transactions as t')
            ->leftJoin('class_transactions as ct','t.class_transactions_id','ct.id')
            ->leftJoin('class_types as ct2','ct2.id','ct.class_type_id')
            ->where('ct2.id',$req->typeID)
            ->where('ct.is_freeze','!=',1)
            ->update([
                't.price' => $req->inputPrice,
                'ct.class_transaction_price' => $req->inputPrice
            ]);

        return redirect()->to($req->return_url)->with('msg','Success Update Data Course');
    }

    public function DeleteType(Request $req){
        $type = ClassType::find($req->typeID)->delete();
        return redirect()->back()->with('msg','Success Delete Course Data');
    }
}
