<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassTransaction;
use App\Models\ClassType;
use App\Models\Rekenings;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class HeadTransactionController extends Controller
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
        return view('head.transaction.index',compact('transactions','sort'));
    }

    public function sorting($column,$type){
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
            ->orderBy($column,$type)
            ->paginate(20);
        $sort = $type == 'asc' ? 'desc' : 'asc';
        return view('head.transaction.index',compact('transactions','sort'));
    }

    public function detailTransaction(Transaction $transaction){
        $detail = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.id',$transaction->id)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('head.transaction.detail',compact('detail','data'));
    }

    public function search(Request $req){
        $transactions = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('students.LongName','like',"%$req->search%")
            ->paginate(20);
        return view('head.transaction.index',compact('transactions'));
    }

    public function updatePage($trans){
        $transaction = Transaction::select('*')
            ->leftJoin('students','students.id','transactions.students_id')
            ->leftJoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftJoin('class_types','class_transactions.class_type_id','class_types.id')
            ->where('transactions.id',$trans)
            ->first();
        $data = Rekenings::where('bank_rek',$transaction->Students->bank_rek)->first();
        return view('head.transaction.update',compact('transaction','data','trans'));
    }

    public function update(Request $req,Transaction $transaction){
        $rules=[
          'inputDisc' => 'required',
          'inputStatus' => 'required',
          'inputJatuhTempo' => 'required',
          'inputPrice' => 'required|numeric',
          'inputQuota' => 'required|numeric|min:1',
        //   'inputBankAccount' => 'required|numeric',
        //   'inputSenderName' => 'required',
        //   'inputBankName' => 'required',
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

        $trans = Transaction::find($transaction->id);

        if($req->has('all_transaction') && !is_null($req->inputTanggalBayar)){
               DB::table('transactions')
                    ->where('students_id',$trans->students_id)
                    ->where('class_transactions_id',$req->class_id)
                    ->update([
                    'discount' => $req->inputDisc,
                    'desc' => $req->inputDesc,
                    'price' => $req->inputPrice,
                    'transaction_date' => $req->inputJatuhTempo,
                    'transaction_type' => $req->Type,
                    'payment_status' => 'Paid',
                    'transaction_payment' => $req->inputTanggalBayar,
                    'transaction_quota' => $req->inputQuota,
                ]);
                $get_trans_paid = DB::table('transactions')
                        ->where('students_id',$trans->students_id)
                        ->where('class_transactions_id',$req->class_id)
                        ->where('payment_status','Paid')
                        ->where(function ($query) {
                            if(now()->setTimezone("GMT+7")->month == 1 || now()->setTimezone("GMT+7")->month == 2 || now()->setTimezone("GMT+7")->month == 3){
                                $query->whereRaw("month(transaction_date) between 1 and 3");
                            }
                            if(now()->setTimezone("GMT+7")->month == 4 || now()->setTimezone("GMT+7")->month == 6 || now()->setTimezone("GMT+7")->month == 5){
                                $query->whereRaw("month(transaction_date) between 4 and 6");
                            }
                            if(now()->setTimezone("GMT+7")->month == 7 || now()->setTimezone("GMT+7")->month == 8 || now()->setTimezone("GMT+7")->month == 9){
                                $query->whereRaw("month(transaction_date) between 7 and 9");
                            }
                            if(now()->setTimezone("GMT+7")->month == 10 || now()->setTimezone("GMT+7")->month == 11 || now()->setTimezone("GMT+7")->month == 12){
                                $query->whereRaw("month(transaction_date) between 10 and 12");
                            }
                        })
                        ->selectRaw("sum(transaction_quota) as quota")
                        ->first();

                        $student_quota_class = DB::table('mapping_class_children')
                            ->where('student_id',$trans->students_id)
                            ->where('class_id',$req->class_id)
                            ->selectRaw("sum(quota) as Quota")
                            ->first();
                            DB::table('mapping_class_children')
                                ->where('student_id',$trans->students_id)
                                ->where('class_id',$req->class_id)
                                ->update([
                                'quota' => $student_quota_class->Quota + $get_trans_paid->quota
                            ]);
                        $student_all_quota_class = DB::table('mapping_class_children')
                            ->where('student_id',$trans->students_id)
                            ->selectRaw("sum(quota) as Quota")
                            ->first();

                        DB::table('students')->where('id',$trans->students_id)->update([
                            'MaxQuota' => $student_all_quota_class->Quota
                        ]);
        } else {
            $trans->discount = $req->inputDisc;
            $trans->desc = $req->inputDesc;
            $trans->price = $req->inputPrice;
            $trans->transaction_date = $req->inputJatuhTempo;
            $trans->transaction_type = $req->Type;
            $trans->payment_status = ucfirst($req->inputStatus);
            $trans->transaction_payment = $req->inputTanggalBayar;
            $trans->transaction_quota = $req->inputQuota;
            if(!is_null($req->inputTanggalBayar)){
                $trans->transaction_payment = $req->inputTanggalBayar;
                $trans->payment_status = 'Paid';
            }
            $trans->save();
        }
        if(!is_null($req->inputTanggalBayar) && !$req->has('all_transaction')){
            $student_quota_class = DB::table('mapping_class_children')
                            ->where('student_id',$trans->students_id)
                            ->where('class_id',$req->class_id)
                            ->selectRaw("sum(quota) as Quota")
                            ->first();

                            $get_trans_paid = DB::table('transactions')
                        ->where('students_id',$trans->students_id)
                        ->where('class_transactions_id',$req->class_id)
                        ->where('payment_status','Paid')
                        ->where(function ($query) {
                            if(now()->setTimezone("GMT+7")->month == 1 || now()->setTimezone("GMT+7")->month == 2 || now()->setTimezone("GMT+7")->month == 3){
                                $query->whereRaw("month(transaction_date) between 1 and 3");
                            }
                            if(now()->setTimezone("GMT+7")->month == 4 || now()->setTimezone("GMT+7")->month == 6 || now()->setTimezone("GMT+7")->month == 5){
                                $query->whereRaw("month(transaction_date) between 4 and 6");
                            }
                            if(now()->setTimezone("GMT+7")->month == 7 || now()->setTimezone("GMT+7")->month == 8 || now()->setTimezone("GMT+7")->month == 9){
                                $query->whereRaw("month(transaction_date) between 7 and 9");
                            }
                            if(now()->setTimezone("GMT+7")->month == 10 || now()->setTimezone("GMT+7")->month == 11 || now()->setTimezone("GMT+7")->month == 12){
                                $query->whereRaw("month(transaction_date) between 10 and 12");
                            }
                        })
                        ->selectRaw("sum(transaction_quota) as quota")
                        ->first();

                            DB::table('mapping_class_children')
                                ->where('student_id',$trans->students_id)
                                ->where('class_id',$req->class_id)
                                ->update([
                                'quota' => $student_quota_class->Quota + $get_trans_paid->quota
                            ]);
                            
                        $student_all_quota_class = DB::table('mapping_class_children')
                            ->where('student_id',$trans->students_id)
                            ->selectRaw("sum(quota) as Quota")
                            ->first();

                        DB::table('students')->where('id',$trans->students_id)->update([
                            'MaxQuota' => $student_all_quota_class->Quota
                        ]);
        }

        return redirect()->route('headTransactionPage')->with('msg','Success Update Transaction');
    }

    public function delete(Request $req,Transaction $transaction){
        DB::table('transactions')->where('id',$transaction->id)->delete();
        return redirect()->route('headTransactionPage')->with('msg','Success Delete Transaction');
    }

    public function Suit(){
        $trans = DB::table('transactions')
            ->where('payment_status','Paid')
            ->selectRaw("
                sum(transaction_quota) as quota,
                class_transactions_id,
                students_id
            ")
            ->groupBy('class_transactions_id','students_id')
            ->get();
        foreach($trans as $t){
            DB::table('mapping_class_children')
                ->where('class_id',$t->class_transactions_id)
                ->where('student_id',$t->students_id)
                ->update([
                    'quota' => $t->quota
                ]);
        }
        return redirect()->back();
    }

    public function addTransaction(){
        $students = Student::where('Status','aktif')->get();
        $class_transaction = ClassType::leftJoin('class_transactions as ct','ct.class_type_id','class_types.id')
                        ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
                        ->leftJoin('users as u','mct.user_id','u.id')
                        ->where('ct.status','aktif')
                        ->selectRaw('ct.id,u.name,class_types.class_name')
                        ->get();
        return view('head.transaction.insert',compact('students','class_transaction'));
    }

    public function getPrice(Request $req){
        $text = substr($req->text,0,strpos($req->text,'-')-1);
        $price = ClassType::where('class_name','like',"%$text%")->first();
        return is_null($price) ? 0 : $price->class_price;
    }

    public function insertTransaction(Request $req){
        $rules = [
            'nis' => 'required',
            'class' => 'required',
            'dateTime' => 'required|before:tomorrow',
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
        $transaction->price = $req->Price;
        // $transaction->discount = @$req->Discount ? $req->Discount : 0;
        // $transaction->desc = @$req->Description ? $req->Description : '-';

        $transaction->save();

        return to_route("headTransactionPage")->with('msg','Success Create Transaction');
    }
}
