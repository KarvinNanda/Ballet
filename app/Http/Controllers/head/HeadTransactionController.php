<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassTransaction;
use App\Models\Rekenings;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class HeadTransactionController extends Controller
{

    public function index(){
        $transactions = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price,
                transactions.discount,
                students.LongName,
                students.id as student_id
            ')
            ->simplePaginate(5);
        return view('head.transaction.index',compact('transactions'));
    }

    public function sorting($column){
        $transactions = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price,
                transactions.discount,
                students.LongName,
                students.id as student_id
            ')
            ->orderBy($column)
            ->simplePaginate(5);
        return view('head.transaction.index',compact('transactions'));
    }

    public function detailTransaction(Transaction $transaction){
        $detail = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('transactions.students_id',$transaction->students_id)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('head.transaction.detail',compact('detail','data'));
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
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('head.transaction.update',compact('transaction','data'));
    }

    public function update(Request $req,Transaction $transaction){
        $rules=[
          'inputDisc' => 'numeric|min:0|max:100',
          'inputStatus' => 'required',
        ];

        $validate = Validator::make($req->all(),$rules);

        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $banks = Banks::updateOrCreate([
            'bank_name' => $req->inputBankName
        ]);

        DB::table('rekenings')->where('bank_rek',$transaction->Students->bank_rek)->update([
            'banks_id' => $banks->id,
            'nama_pengirim' => $req->inputSenderName
        ]);

        $trans = Transaction::find($transaction->id);
        $trans->discount = $req->inputDisc;
        $trans->desc = $req->inputDesc;
        $trans->save();
        return redirect()->route('headTransactionPage');
    }

    public function addTransaction(){
        $students = Student::all();
        $class_transaction = ClassTransaction::all();
        return view('head.transaction.insert',compact('students','class_transaction'));
    }

    public function insertTransaction(Request $req){
        $rules = [
            'nis' => 'required',
            'class' => 'required',
            'dateTime' => 'required',
            'Price' => 'required|numeric',
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $transaction = new Transaction();
        $transaction->students_id = $req->nis;
        $transaction->class_transactions_id = $req->class;
        $transaction->transaction_date = $req->dateTime;
        $transaction->payment_status = "Unpaid";
        $transaction->discount = @$req->Discount ? $req->Discount : 0;
        $transaction->price = $req->Price;
        $transaction->desc = @$req->Description ? $req->Description : '-';

        $transaction->save();

        return to_route("headTransactionPage");
    }
}
