<?php

namespace App\Http\Controllers\staff;

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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $req){
        $sort = 'asc';
        $keyword = $req->search;
        $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->leftjoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftjoin('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price as price,
                transactions.discount,
                students.LongName,
                students.id as student_id
            ')
            ->where(function ($query) use ($keyword){
                if(!is_null($keyword)){
                    $query->where('students.Longname','like',"%$keyword%");
                }
            })
            ->where('students.Status','aktif')
            ->orderBy('transactions.id','desc')
            ->paginate(20);
        return view('staff.transaction.index',compact('transactions','sort'));
    }

    public function sort($column,$direction){
        [$column, $direction] = $this->sortOrFail($column, $direction, ['payment_status', 'price']);
        $transactions = Transaction::join('students','students.id','transactions.students_id')
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
            ->where('students.Status','aktif')
            ->orderBy($column,$direction)
            ->paginate(20);
        $sort = $direction == 'asc' ? 'desc' : 'asc';
        return view('staff.transaction.index',compact('transactions','sort'));
    }

    public function show(Transaction $transaction){
        $detail = $this->joinedRow($transaction);
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('staff.transaction.detail',compact('detail','data'));
    }

    public function create(){
        $students = Student::where('Status','aktif')->get();
        $class_transaction = ClassType::leftJoin('class_transactions as ct','ct.class_type_id','class_types.id')
                        ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
                        ->leftJoin('users as u','mct.user_id','u.id')
                        ->where('ct.status','aktif')
                        ->selectRaw('ct.id,u.name,class_types.class_name')
                        ->get();
        return view('staff.transaction.insert',compact('students','class_transaction'));
    }

    public function store(Request $req){
        $rules = [
            'nis' => 'required|integer|exists:students,id', // the form sends the student id
            'class' => 'required|integer|exists:class_transactions,id', // the form sends the class_transactions id
            'dateTime' => 'required|before:tomorrow',
            'Price' => 'required|integer|min:0|max:2000000000',
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
        $transaction->price = $req->Price;
        $transaction->save();

        return redirect(staff_route('transaction.index'))->with('msg','Success Create Transaction');
    }

    /** AJAX for the class dropdown. Option text is "<class name> - <teacher>"; answers the plain price. */
    public function price(Request $req)
    {
        if (! is_string($req->query('text')) || ! $req->filled('text')) {
            return response()->json(['message' => 'The text field is required.'], 422);
        }

        $className = trim(Str::before($req->query('text'), ' - '));

        return response((string) (ClassType::where('class_name', $className)->value('class_price') ?? 0));
    }

    public function edit(Transaction $transaction){
        Gate::authorize('transaction.edit-paid', $transaction);
        $return_url = url()->previous();
        $row = $this->joinedRow($transaction);
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('staff.transaction.update',compact('transaction','row','data','return_url'));
    }

    public function update(Request $req,Transaction $transaction){
        Gate::authorize('transaction.edit-paid', $transaction);

        $rules=[
            'inputDisc' => 'required|string|max:255',
            'inputStatus' => 'required|in:Paid,Unpaid,paid,unpaid',
            'inputJatuhTempo' => 'required|date',
            'inputSenderName' => 'nullable|string|max:255',
            'inputBankName' => 'nullable|string|max:255',
            'inputQuota' => 'required|integer|min:1|max:24',
            'inputPrice' => 'required|integer|min:0|max:2000000000',
            'inputTanggalBayar' => 'nullable|date',
            'inputDesc' => 'nullable|string|max:255',
            'Type' => 'nullable|string|max:255',
        ];

        $validate = Validator::make($req->all(),$rules);

        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        if(!$req->filled('inputTanggalBayar') && ucfirst($req->inputStatus) == 'Paid'){
            return back()->withInput()->with('error', 'Please fill the payment date when the status is Paid');
        }

        $bankId = $req->filled('inputBankName')
            ? Banks::firstOrCreate(['bank_name' => $req->inputBankName])->id
            : Rekenings::where('bank_rek', $transaction->Students->bank_rek)->value('banks_id');

        if($req->filled('inputSenderName')){
            DB::table('rekenings')->where('bank_rek',$transaction->Students->bank_rek)->update([
                'banks_id' => $bankId,
                'nama_pengirim' => $req->inputSenderName
            ]);
        }

        $hasPaymentDate = $req->filled('inputTanggalBayar');

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
            }
            $transaction->save();
        }

        if($hasPaymentDate && !is_null($transaction->class_transactions_id)){
            TransactionQuota::recalculate($transaction->students_id, $transaction->class_transactions_id);
        }

        return $this->backTo($req->return_url)->with('msg','Success Update Transaction');
    }

    public function destroy(Transaction $transaction){
        Gate::authorize('transaction.delete');
        $transaction->delete();
        return redirect()->back()->with('msg','Success Delete Transaction');
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
