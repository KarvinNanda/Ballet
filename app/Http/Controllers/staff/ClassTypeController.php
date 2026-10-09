<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ClassTypeIdRequest;
use App\Http\Requests\Staff\UpdateClassTypeRequest;
use App\Models\ClassType;
use Illuminate\Support\Facades\DB;

/** Courses (class types). Add lives in ClassController::createCourse/storeCourse. */
class ClassTypeController extends Controller
{
    public function index(){
        $types = ClassType::orderBy('id')->paginate(5);
        return view('staff.classType.index',compact('types'));
    }

    public function edit(ClassTypeIdRequest $req){
        $return_url = url()->previous();
        $type = ClassType::findOrFail($req->validated('typeID'));
        return view('staff.classType.update',compact('type','return_url'));
    }

    public function update(UpdateClassTypeRequest $req){
        DB::transaction(function () use ($req) {
            $type = ClassType::findOrFail($req->typeID);
            $price = (int) $req->inputPrice;
            $type->class_price = $price;
            $type->save();

            // An unchanged price must not reset hand-edited class or Unpaid prices.
            if (! $type->wasChanged('class_price')) {
                return;
            }

            // Non-frozen classes of this course take the new price.
            $classIds = DB::table('class_transactions')
                ->where('class_type_id',$req->typeID)
                ->where('is_freeze','!=',1)
                ->pluck('id');
            DB::table('class_transactions')->whereIn('id',$classIds)->update(['class_transaction_price' => $price]);

            // Only Unpaid transactions follow; Paid ones keep the amount that was paid (any role).
            DB::table('transactions')
                ->whereIn('class_transactions_id',$classIds)
                ->where('payment_status','Unpaid')
                ->update(['price' => $price]);
        });

        return $this->backTo($req->return_url)->with('msg','Success Update Course Data');
    }

    public function destroy(ClassTypeIdRequest $req){
        $type = ClassType::findOrFail($req->validated('typeID'));

        // class_transactions has no FK to class_types: refuse instead of orphaning classes.
        $count = DB::table('class_transactions')->where('class_type_id', $type->id)->count();
        if ($count > 0) {
            $classes = $count === 1 ? '1 class still uses' : "{$count} classes still use";
            return redirect()->back()->with('error', "{$classes} this course; delete or move them first.");
        }

        $type->delete();
        return redirect()->back()->with('msg','Success Delete Course Data');
    }
}
