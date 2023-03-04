<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinanceTransactionController extends Controller
{
    public function index(){
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price,
                transactions.discount,
                students.LongName
            ')
            ->simplePaginate(5);
        return view('finance.transaction',compact('transactions'));
    }

    public function sorting($column){
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price,
                transactions.discount,
                students.LongName
            ')
            ->orderBy($column)
            ->simplePaginate(5);
        return view('finance.transaction',compact('transactions'));
    }

    public function search(Request $req){
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('students.LongName','like',"%$req->search%")
            ->simplePaginate(5);
        return view('finance.transaction',compact('transactions'));
    }

    public function viewPaidTransaction(Transaction $transaction){
        return view('finance.paid',compact('transaction'));
    }

    public function submitPaidTransaction(Transaction $transaction,Request $req){
        $rules = [
            'datePaid' => 'required',
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }
        $transaction = Transaction::find($transaction->id);
        $transaction->transaction_payment = $req->datePaid;
        $transaction->payment_status = "Paid";
        $transaction->save();
        return redirect()->route('financeTransaction');
    }
}
