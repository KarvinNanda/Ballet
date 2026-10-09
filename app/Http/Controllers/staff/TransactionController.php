<?php

namespace App\Http\Controllers\staff;

use App\Support\Like;
use App\Http\Requests\Staff\TransactionListRequest;
use App\Http\Requests\Staff\StoreTransactionRequest;
use App\Http\Requests\Staff\UpdateTransactionRequest;
use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassType;
use App\Models\Rekenings;
use App\Models\Student;
use App\Models\Transaction;
use App\Support\TransactionQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    private const PER_PAGE = 20;

    public function index(TransactionListRequest $req){
        $sort = 'asc';
        $transactions = $this->filtered($this->listQuery(), $req)
            ->orderBy('transactions.id','desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('staff.transaction.index',compact('transactions','sort'));
    }

    public function sort(TransactionListRequest $req, $column, $direction){
        [$column, $direction] = $this->sortOrFail($column, $direction, ['payment_status', 'price']);
        $transactions = $this->filtered($this->listQuery(), $req)
            ->orderBy('transactions.'.$column, $direction)
            ->orderBy('transactions.id','desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
        $sort = $direction == 'asc' ? 'desc' : 'asc';

        return view('staff.transaction.index',compact('transactions','sort'));
    }

    /** Rows of the list page: transactions of active students, with the class name. */
    private function listQuery(){
        return Transaction::join('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price as price,
                transactions.discount,
                students.LongName,
                students.id as student_id,
                class_types.class_name
            ')
            ->where('students.Status','aktif');
    }

    /** Search and status filters; TransactionListRequest has already dropped invalid values. */
    private function filtered($query, TransactionListRequest $req){
        $status = $req->query('status', 'all');

        return $query
            ->when($status !== 'all', fn ($q) => $q->where('transactions.payment_status', $status))
            ->when(filled($keyword = $req->query('search')), fn ($q) => $q->where('students.LongName','like',Like::contains($keyword)));
    }

    public function show(Transaction $transaction){
        $detail = $this->joinedRow($transaction);
        $data = $this->rekeningOf($transaction);
        return view('staff.transaction.detail',compact('detail','data','transaction'));
    }

    public function create(){
        $students = Student::where('Status','aktif')->get();
        $class_transaction = ClassType::leftJoin('class_transactions as ct','ct.class_type_id','class_types.id')
                        ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
                        ->leftJoin('users as u','mct.user_id','u.id')
                        ->where('ct.status','aktif')
                        ->selectRaw('ct.id,u.name,class_types.class_name,ct.class_transaction_price as class_price')
                        ->get();
        return view('staff.transaction.insert',compact('students','class_transaction'));
    }

    public function store(StoreTransactionRequest $req){
        $transaction = new Transaction();
        $transaction->students_id = $req->nis;
        $transaction->class_transactions_id = $req->class;
        $transaction->transaction_date = $req->dateTime;
        $transaction->payment_status = "Unpaid";
        $transaction->price = $req->Price;
        $transaction->save();

        return redirect(staff_route('transaction.index'))->with('msg','Success Create Transaction');
    }

    public function edit(Transaction $transaction){
        Gate::authorize('transaction.edit-paid', $transaction);
        $return_url = url()->previous();
        $row = $this->joinedRow($transaction);
        $data = $this->rekeningOf($transaction);
        return view('staff.transaction.update',compact('transaction','row','data','return_url'));
    }

    public function update(UpdateTransactionRequest $req,Transaction $transaction){
        $hasPaymentDate = $req->filled('inputTanggalBayar');

        TransactionQuota::track($transaction->students_id, $transaction->class_transactions_id, function () use ($req, $transaction, $hasPaymentDate) {
            // The request checked the row as it was loaded; check again under the lock so an admin cannot
            // overwrite a row that another request settled in between (403, nothing written).
            Gate::authorize('transaction.edit-paid', Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail());

            // Only the student's own account row; a student without a number (NULL or '') has none.
            $bankRek = $transaction->Students?->bank_rek;
            if(filled($bankRek) && $req->filled('inputSenderName')){
                $bankId = $req->filled('inputBankName')
                    ? Banks::firstOrCreate(['bank_name' => $req->inputBankName])->id
                    : Rekenings::where('bank_rek', $bankRek)->value('banks_id');
                DB::table('rekenings')->where('bank_rek', $bankRek)->update([
                    'banks_id' => $bankId,
                    'nama_pengirim' => $req->inputSenderName
                ]);
            }

            if($req->has('all_transaction') && $hasPaymentDate){
                $bulk = DB::table('transactions')
                    ->where('students_id',$transaction->students_id)
                    ->where('class_transactions_id',$transaction->class_transactions_id);
                // Whoever may not edit settled rows (admin) only rewrites siblings it could edit one by one: 'Unpaid' rows.
                if(Gate::denies('transaction.edit-paid', (new Transaction)->forceFill(['payment_status' => 'Paid']))){
                    $bulk->where('payment_status','Unpaid');
                }
                $bulk->update([
                        'discount' => $req->inputDisc,
                        'desc' => $req->inputDesc,
                        'price' => $req->inputPrice,
                        'transaction_date' => $req->inputJatuhTempo,
                        'transaction_type' => $req->Type,
                        'payment_status' => 'Paid',
                        'transaction_payment' => $req->inputTanggalBayar,
                        'transaction_quota' => $req->inputQuota,
                    ]);
            } else {
                $transaction->discount = $req->inputDisc;
                $transaction->desc = $req->inputDesc;
                $transaction->price = $req->inputPrice;
                $transaction->transaction_date = $req->inputJatuhTempo;
                $transaction->transaction_type = $req->Type;
                $transaction->payment_status = ucfirst($req->inputStatus);
                $transaction->transaction_quota = $req->inputQuota;
                if($hasPaymentDate){
                    $transaction->transaction_payment = $req->inputTanggalBayar;
                    $transaction->payment_status = 'Paid';
                } elseif($transaction->payment_status === 'Unpaid'){
                    $transaction->transaction_payment = null; // an unpaid row has no payment date (the request refuses one)
                }
                $transaction->save();
            }
        });

        return $this->backTo($req->return_url)->with('msg','Success Update Transaction');
    }

    public function destroy(Request $req, Transaction $transaction){
        Gate::authorize('transaction.delete');
        TransactionQuota::track($transaction->students_id, $transaction->class_transactions_id, fn () => $transaction->delete());
        // From the detail page "back" would be the deleted record (404), so that form sends return_url.
        $back = $req->filled('return_url') ? $this->backTo($req->input('return_url')) : redirect()->back();
        return $back->with('msg','Success Delete Transaction');
    }

    /** The student's own account row, or null when the student is gone or has no account number. */
    private function rekeningOf(Transaction $transaction): ?Rekenings
    {
        $bankRek = $transaction->Students?->bank_rek;

        return filled($bankRek) ? Rekenings::where('bank_rek', $bankRek)->first() : null;
    }

    /** Transaction joined with its student and class names, for the detail and update pages. */
    private function joinedRow(Transaction $transaction)
    {
        return Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.id',$transaction->id)
            ->first();
    }
}
