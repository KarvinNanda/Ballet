<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\ClassTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HeadClassController extends Controller
{
    public function index(){
        $classes = ClassTransaction::simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function active(){
        $classes = ClassTransaction::where('Status','aktif')->simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function nonActive(){
        $classes = ClassTransaction::where('Status','non-aktif')->simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function search(Request $req){
        $classes = ClassTransaction::where('ClassName','like',"%$req->search%")->simplePaginate(5);
        return view('head.class.index',compact('classes'));
    }

    public function insertPage(){
        return view('head.class.insert');
    }

    public function insert(Request $req){
        $rules = [
            'inputName' => 'required',
            'inputPrice' => 'required|numeric'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $class = new ClassTransaction();
        $class->ClassName = $req->inputName;
        $class->ClassPrice = $req->inputPrice;
		$class->Status = 'aktif';

        $class->save();

        return redirect()->route('headClassPage');
    }

    public function ChangeStatus(ClassTransaction $class){
        $change = ClassTransaction::find($class->id);
        if($change->Status == 'aktif'){
            $change->Status = 'non-aktif';
        } else {
            $change->Status = 'aktif';
        }
        $change->save();
        return redirect()->back();
    }
}
