<?php

namespace App\Http\Controllers\finance;

use App\Support\Like;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\MarkTransactionPaidRequest;
use App\Http\Requests\Staff\TransactionListRequest;
use App\Models\Banks;
use App\Models\Rekenings;
use App\Models\Transaction;
use App\Support\TransactionQuota;
use Illuminate\Support\Facades\DB;

class FinanceTransactionController extends Controller
{
    private const PER_PAGE = 20;

    public function index(TransactionListRequest $req){
        return $this->listPage($req);
    }

    public function search(TransactionListRequest $req){
        // Same page as index(): the filter bar sends search and status, and old links to this URL keep working.
        return $this->listPage($req);
    }

    public function sorting(TransactionListRequest $req, $column){
        [$column] = $this->sortOrFail($column, 'asc', ['payment_status', 'price']);

        return $this->listPage($req, $column);
    }

    public function viewPaidTransaction(Transaction $transaction){
        $return_url = url()->previous();
        $student = $transaction->Students;
        $studentName = $student?->LongName ?? 'Unknown student';
        $className = $transaction->ClassTransactions?->Type?->class_name;
        // No account number, no rekening: a null number must not pick up some other row.
        $data = filled($student?->bank_rek) ? Rekenings::where('bank_rek', $student->bank_rek)->first() : null;

        return view('finance.paid', compact('transaction', 'student', 'studentName', 'className', 'data', 'return_url'));
    }

    public function submitPaidTransaction(MarkTransactionPaidRequest $req, Transaction $transaction){
        if ($transaction->payment_status !== 'Unpaid') {
            return redirect()->back()->with('error', 'This transaction is already settled.');
        }

        $marked = TransactionQuota::track($transaction->students_id, $transaction->class_transactions_id, function () use ($req, $transaction) {
            // Check again under the lock: a double submit passes the check above twice, only the first may write.
            if (DB::table('transactions')->where('id', $transaction->id)->lockForUpdate()->value('payment_status') !== 'Unpaid') {
                return false;
            }

            $bank = Banks::firstOrCreate(['bank_name' => $req->inputBankName]);
            // Only this student's own account row; the rekening may be shared by siblings (same number).
            // A student without an account number has no rekening of their own: skip, never match another row.
            $bankRek = $transaction->Students?->bank_rek;
            if (filled($bankRek)) {
                DB::table('rekenings')->where('bank_rek', $bankRek)->update([
                    'banks_id' => $bank->id,
                    'nama_pengirim' => $req->inputSenderName,
                ]);
            }

            $transaction->transaction_payment = $req->datePaid;
            $transaction->payment_status = 'Paid';
            $transaction->transaction_type = $req->Type;
            $transaction->transaction_quota = $req->inputQuota;
            $transaction->save();

            return true;
        });

        if (! $marked) {
            return redirect()->back()->with('error', 'This transaction is already settled.');
        }

        return $this->backTo($req->return_url)->with('msg','Success Update Transaction');
    }

    /** The list page shared by index, search and sorting: one filter (status, default Unpaid, + search) and one query. */
    private function listPage(TransactionListRequest $req, ?string $sortColumn = null){
        // TransactionListRequest has already dropped a status outside all/Unpaid/Paid and junk search values.
        $status = $req->query('status', 'Unpaid');

        $transactions = $this->listQuery()
            ->when($status !== 'all', fn ($q) => $q->where('transactions.payment_status', $status))
            ->when(filled($keyword = $req->query('search')), fn ($q) => $q->where('students.LongName', 'like', Like::contains($keyword)))
            ->when($sortColumn, fn ($q, $column) => $q->orderBy('transactions.'.$column))
            ->orderBy('transactions.id', 'desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('finance.transaction', compact('transactions', 'status'));
    }

    /**
     * One row per transaction: the student is required (as before), class and course are optional
     * (a transaction without a class still shows). No teacher join: a class with two teachers would list twice.
     */
    private function listQuery(){
        return Transaction::join('students', 'students.id', 'transactions.students_id')
            ->leftJoin('class_transactions', 'class_transactions.id', 'transactions.class_transactions_id')
            ->leftJoin('class_types', 'class_types.id', 'class_transactions.class_type_id')
            ->select([
                'transactions.id',
                'transactions.transaction_date',
                'transactions.transaction_payment',
                'transactions.payment_status',
                'transactions.price',
                'transactions.discount',
                'students.LongName',
                'class_types.class_name',
            ]);
    }
}
