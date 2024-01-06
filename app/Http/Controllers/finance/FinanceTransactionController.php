<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\Rekenings;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinanceTransactionController extends Controller
{
    public function index(){
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->leftjoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftjoin('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                class_types.class_price as price,
                transactions.discount,
                students.LongName
            ')
            ->orderBy('transactions.id','desc')
            ->paginate(5);
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
            ->paginate(5);
        return view('finance.transaction',compact('transactions'));
    }

    public function search(Request $req){
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('students.LongName','like',"%$req->search%")
            ->paginate(5);
        return view('finance.transaction',compact('transactions'));
    }

    public function viewPaidTransaction(Transaction $transaction){
        $trans = $transaction->id;
        $transaction = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.id',$trans)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('finance.paid',compact('transaction','data','trans'));
    }

    public function submitPaidTransaction($trans,Request $req){
        $transaction = Transaction::find($trans);
        $rules = [
            'datePaid' => 'required',
            'inputBankName' => 'required',
            'inputSenderName' => 'required',
            'Type' => 'required'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $banks = Banks::updateOrCreate([
            'bank_name' => $req->inputBankName
        ]);

        DB::table('rekenings')->where('bank_rek',$transaction->Students->bank_rek)->update([
            'banks_id' => $banks->id,
            'nama_pengirim' => $req->inputSenderName
        ]);

//        $transaction = Transaction::find($transaction->id);
        $transaction->transaction_payment = $req->datePaid;
        $transaction->payment_status = "Paid";
        $transaction->transaction_type = $req->Type;
        $transaction->save();
        return redirect()->route('financeTransaction')->with('msg','Success Update Transaction');
    }
}
