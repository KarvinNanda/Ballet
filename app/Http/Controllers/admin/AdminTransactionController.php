<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassTransaction;
use App\Models\Rekenings;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminTransactionController extends Controller
{
    public function index(){
        $sort = 'asc';
        $transactions = DB::table('transactions')
            ->join('students','students.id','transactions.students_id')
            ->leftjoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftjoin('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                class_types.class_price as price,
                transactions.discount,
                students.LongName,
                students.id as student_id
            ')
            ->orderBy('transactions.id','desc')
            ->paginate(5);
        return view('admin.transaction.index',compact('transactions','sort'));
    }

    public function adminTransactionSorting($value,$type){
        $transactions = DB::table('transactions')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->selectRaw('
                   transactions.id as id,
                   students.LongName as LongName,
                   transactions.transaction_date as transaction_date,
                   transactions.transaction_payment as transaction_payment,
                   transactions.price as price,
                   transactions.discount as discount,
                   transactions.desc as description,
                   transactions.payment_status as payment_status,
                   students.id as student_id
            ')->orderBy($value,$type)
            ->paginate(5);
        $sort = $type == 'asc' ? 'desc' : 'asc';
        return view('admin.transaction.index',compact('transactions','sort'));
    }

    public function addTransaction(){
        $students = Student::all();
        $class_transaction = ClassTransaction::all();
        return view('admin.transaction.insert',compact('students','class_transaction'));
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
            return redirect()->back()->withErrors($validate)->withInput();
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

        return redirect()->route('adminTransactionPage')->with('msg','Success Create Transaction');
    }

    public function searchTransaction(Request $req){

        $transactions = Transaction::select('*')
            ->join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('students.LongName','LIKE','%'.$req->search.'%')
            ->paginate(5);
        return view('admin.transaction.index',compact('transactions'));
    }

    public function viewPaidTransaction($transactionId){
        return view('admin.transaction.paid',compact('transactionId'));
    }

    public function detailTransaction(Transaction $transaction){
        $detail = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.students_id',$transaction->students_id)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('admin.transaction.detail',compact('detail','data'));
    }

    public function updatePage($trans){
        $transaction = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.id',$trans)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('admin.transaction.update',compact('transaction','data','trans'));
    }

    public function update(Request $req,Transaction $transaction){
        $rules=[
            'inputDisc' => 'numeric|min:0|max:100',
            'inputStatus' => 'required',
            'inputJatuhTempo' => 'required',
            'inputSenderName' => 'required',
        ];

        $validate = Validator::make($req->all(),$rules);

        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        if(!is_null($req->inputBankName)){
            $banks = Banks::updateOrCreate([
                'bank_name' => $req->inputBankName
            ]);
        }

        if(!is_null($req->inputSenderName)){
            DB::table('rekenings')->where('bank_rek',$transaction->Students->bank_rek)->update([
                'banks_id' => $banks->id,
                'nama_pengirim' => $req->inputSenderName
            ]);
        }

        $trans = Transaction::find($transaction->id);
        $trans->payment_status = ucfirst($req->inputStatus);
        if(!is_null($req->inputTanggalBayar)){
            $trans->transaction_payment = $req->inputTanggalBayar;
            $trans->payment_status = 'Paid';
        }
        $trans->desc = $req->inputDesc;
        $trans->transaction_date = $req->inputJatuhTempo;
        $trans->save();
        return redirect()->route('adminTransactionPage')->with('msg','Success Update Transaction');
    }

    public function submitPaidTransaction($transactionId,Request $req){
        $rules = [
            'datePaid' => 'required',
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }
        $transaction = Transaction::find($transactionId);
//        dd($transaction);
        $transaction->transaction_payment = $req->datePaid;
        $transaction->payment_status = "lunas";
        $transaction->save();
        return redirect()->route('adminTransactionPage');
    }


}
