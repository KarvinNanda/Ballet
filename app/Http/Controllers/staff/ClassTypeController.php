<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/** Courses (class types). Add lives in ClassController::createCourse/storeCourse. */
class ClassTypeController extends Controller
{
    public function index(){
        $types = ClassType::orderBy('id')->paginate(5);
        return view('staff.classType.index',compact('types'));
    }

    public function edit(Request $req){
        $return_url = url()->previous();
        $type = ClassType::findOrFail($req->typeID);
        return view('staff.classType.update',compact('type','return_url'));
    }

    public function update(Request $req){
        $validate = Validator::make($req->all(), [
            'typeID' => 'required|integer|exists:class_types,id',
            'inputPrice' => 'required|integer|min:0|max:2000000000',
        ]);
        // The edit page is reached by POST, so "back" would be a GET on a POST-only URL (405): go to the list.
        if($validate->fails()){
            return redirect(staff_route('class-type.index'))->withErrors($validate)->with('error', $validate->errors()->first());
        }

        DB::transaction(function () use ($req) {
            $type = ClassType::findOrFail($req->typeID);
            $type->class_price = $req->inputPrice;
            $type->save();

            // Non-frozen classes of this course take the new price.
            $classIds = DB::table('class_transactions')
                ->where('class_type_id',$req->typeID)
                ->where('is_freeze','!=',1)
                ->pluck('id');
            DB::table('class_transactions')->whereIn('id',$classIds)->update(['class_transaction_price' => $req->inputPrice]);

            // Only Unpaid transactions follow; Paid ones keep the amount that was paid (any role).
            DB::table('transactions')
                ->whereIn('class_transactions_id',$classIds)
                ->where('payment_status','Unpaid')
                ->update(['price' => $req->inputPrice]);
        });

        return $this->backTo($req->return_url)->with('msg','Success Update Course Data');
    }

    public function destroy(Request $req){
        ClassType::findOrFail($req->typeID)->delete();
        return redirect()->back()->with('msg','Success Delete Course Data');
    }
}
