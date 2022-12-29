<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class HeadTransactionController extends Controller
{

    public function index(){
        $transactions = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->simplePaginate(5);
        return view('head.transaction.index',compact('transactions'));
    }

    public function search(Request $req){
        $transactions = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('students.LongName','like',"%$req->search%")
            ->simplePaginate(5);
        return view('head.transaction.index',compact('transactions'));
    }

    public function updatePage($trans){

        $transaction = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('transactions.id',$trans)
            ->first();

        return view('head.transaction.update',compact('transaction'));
    }

    public function update(Request $req,Transaction $transaction){
        $rules=[
          'inputDisc' => 'required|numeric|min:0|max:100',
          'inputDesc' => 'required',
          'inputStatus' => 'required',
        ];

        $validate = Validator::make($req->all(),$rules);

        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $trans = Transaction::find($transaction->id);
        $trans->discount = $req->inputDisc;
        $trans->desc = $req->inputDesc;
        $trans->save();
        return redirect()->route('headTransactionPage');
    }
}
