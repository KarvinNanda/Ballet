<?php

namespace App\Http\Controllers\finance;

use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\MarkTransactionPaidRequest;
use App\Models\Banks;
use App\Models\Rekenings;
use App\Models\Transaction;
use App\Support\TransactionQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        [$column] = $this->sortOrFail($column, 'asc', ['payment_status', 'price']);
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

    public function search(SearchRequest $req){
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->join('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->where('students.LongName','like',"%$req->search%")
            ->paginate(5);
        return view('finance.transaction',compact('transactions'));
    }

    public function viewPaidTransaction(Transaction $transaction){
        $return_url = url()->previous();
        $trans = $transaction->id;
        $transaction = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.id',$trans)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('finance.paid',compact('transaction','data','trans','return_url'));
    }

    public function submitPaidTransaction(MarkTransactionPaidRequest $req, Transaction $transaction){
        if ($transaction->payment_status !== 'Unpaid') {
            return redirect()->back()->with('error', 'This transaction is already settled.');
        }

        TransactionQuota::track($transaction->students_id, $transaction->class_transactions_id, function () use ($req, $transaction) {
            $bank = Banks::firstOrCreate(['bank_name' => $req->inputBankName]);
            // Only this student's own account row; the rekening may be shared by siblings (same number).
            DB::table('rekenings')->where('bank_rek', $transaction->Students?->bank_rek)->update([
                'banks_id' => $bank->id,
                'nama_pengirim' => $req->inputSenderName,
            ]);

            $transaction->transaction_payment = $req->datePaid;
            $transaction->payment_status = 'Paid';
            $transaction->transaction_type = $req->Type;
            $transaction->transaction_quota = $req->inputQuota;
            $transaction->save();
        });

        return $this->backTo($req->return_url)->with('msg','Success Update Transaction');
    }
}
