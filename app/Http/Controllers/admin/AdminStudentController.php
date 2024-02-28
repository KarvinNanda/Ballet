<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassTransaction;
use App\Models\MappingClassChild;
use App\Models\Rekenings;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminStudentController extends Controller
{
    public function viewStudentForm(){
        return view('admin.student.studentForm');
    }

    public function adminStudentView(Request $request){
        $sort = 'asc';
        $keyword = $request->query('keyword');
        $status = $request->query('status');
        if(is_null($status))$status='all';

        if ($status == "all") {
            $students = DB::table('students')
                ->leftJoin('rekenings','students.bank_rek','rekenings.bank_rek')
                ->leftJoin('banks','rekenings.banks_id','banks.id')
                ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.Line as line,
                students.Instagram as instagram,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim
            ')
                ->where('students.LongName',"LIKE","%$keyword%")
                ->orWhere('students.ShortName',"LIKE","%$keyword%")
                ->orWhere('students.Instagram',"LIKE","%$keyword%")
                ->orWhere('students.Phone1',"LIKE","%$keyword%")
                ->orWhere('students.Phone2',"LIKE","%$keyword%")
                ->orWhere('students.bank_rek',"LIKE","%$keyword%")
                ->orWhere('students.nama_orang_tua',"LIKE","%$keyword%")
                ->orWhere('students.Address',"LIKE","%$keyword%")
                ->orWhere('rekenings.nama_pengirim',"LIKE","%$keyword%")
                ->orWhere('banks.bank_name',"LIKE","%$keyword%")
                ->orderBy('students.id','desc')
                ->distinct()
                ->paginate(5);
        } else {
            $students = DB::table('students')
                ->leftJoin('rekenings','students.bank_rek','rekenings.bank_rek')
                ->leftJoin('banks','rekenings.banks_id','banks.id')
                ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.Line as line,
                students.Instagram as instagram,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim
            ')
                ->where('students.Status', "=", $status)
                ->where(function ($query) use ($keyword) {
                    $query->where('students.LongName',"LIKE","%$keyword%")
                        ->orWhere('students.ShortName',"LIKE","%$keyword%")
                        ->orWhere('students.Instagram',"LIKE","%$keyword%")
                        ->orWhere('students.Phone1',"LIKE","%$keyword%")
                        ->orWhere('students.Phone2',"LIKE","%$keyword%")
                        ->orWhere('students.bank_rek',"LIKE","%$keyword%")
                        ->orWhere('students.nama_orang_tua',"LIKE","%$keyword%")
                        ->orWhere('students.Address',"LIKE","%$keyword%")
                        ->orWhere('rekenings.nama_pengirim',"LIKE","%$keyword%")
                        ->orWhere('banks.bank_name',"LIKE","%$keyword%");
                })
                ->orderBy('students.id','desc')
                ->distinct()
                ->paginate(5);
        }
        return view('admin.student.adminStudentView',compact('students','sort'));
    }

    public function insertClassPage($studentId){

        $classes = DB::table('mapping_class_children as mcc')
                    ->where('mcc.student_id',$studentId)
                    ->selectRaw("mcc.class_id,mcc.student_id")
                    ->distinct()->get();
                    // dd($classes);

        if(count($classes) > 0){
            // dd('123');
            $data = DB::table('class_transactions as ct')
                        // ->leftJoin('schedules as s','ct.id','s.class_id')
                        ->leftJoin('class_types as ct2','ct2.id','ct.class_type_id')
                        // ->leftJoin('mapping_class_children as mcc',function($q) use ($studentId){
                        //     $q->on('mcc.class_id','ct.id')
                        //     ->where('mcc.student_id','!=',$studentId);
                        // })
                        ->leftJoin('mapping_class_children as mcc','mcc.class_id','ct.id')
                        ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
                        ->leftJoin('users as u','mct.user_id','u.id')
                        ->whereNotIn('ct.id',$classes->pluck('class_id')->toArray())
                        // ->whereNotNull('class_name')
                        ->HavingRaw('count(mcc.student_id) > 0')
                        ->selectRaw("ct.id,ct2.class_name,u.name as user,count(mcc.student_id) as students")
                        ->where('mcc.student_id','!=',0)
                        ->groupBy('ct.id','ct2.class_name','u.name')
                        ->distinct()
                        ->orderBy('ct2.id')
                        ->get();
        } else {
            $data = DB::table('class_transactions as ct')
                        ->leftJoin('class_types as ct2','ct2.id','ct.class_type_id')
                        // ->leftJoin('schedules as s','ct.id','s.class_id')
                        // ->leftJoin('mapping_class_children as mcc',function($q) use ($studentId){
                        //     $q->on('mcc.class_id','ct.id')
                        //     ->where('mcc.student_id','!=',$studentId);
                        // })
                        ->leftJoin('mapping_class_children as mcc','mcc.class_id','ct.id')
                        ->leftJoin('mapping_class_teachers as mct','mct.class_id','ct.id')
                        ->leftJoin('users as u','mct.user_id','u.id')
                        // ->whereNotIn('mcc.class_id',$classes->pluck('class_id'))
                        // ->whereNotNull('ct.id')
                        ->HavingRaw('count(mcc.student_id) > 0')
                        ->selectRaw("ct.id,ct2.class_name,u.name as user,count(mcc.student_id) as students")
                        ->where('mcc.student_id','!=',0)
                        ->groupBy('ct.id','ct2.class_name','u.name')
                        ->distinct()
                        ->orderBy('ct2.id')
                        ->get();
        } 
        if(count($data) == 0){
            return redirect()->route("adminStudentDetail", ['studentId' => $studentId])->with('msg','No Classes Available');
        }
        $schedules = DB::table('schedules')->whereIn('class_id',$data->pluck('id')->toArray())->select('class_id')->distinct()->get()->pluck('class_id')->toArray();

        return view('admin.student.class',compact('data','studentId','schedules'));
    }

    public function insertClass($class_id,$studentId){
        $get_class_price = ClassTransaction::leftJoin('class_types','class_types.id','class_transactions.class_type_id')
            ->where('class_transactions.id',$class_id)->first();

        $get_student = Student::where('id',$studentId)->first();
        $quota=0;
        if($get_class_price->class_name == 'Pointe Class') $quota = 4;
        else if($get_class_price->class_name == 'Intensive Kids' || $get_class_price->class_name == 'Intensive Class')$quota = 12;
        else $quota = 8;
        $check_schedule = Schedule::where('class_id',$class_id)
            ->whereRaw('date  >= curdate()')
            ->orderBy('date')
            ->first();

        if(!is_null($check_schedule) && $get_student->Status == 'aktif'){
            // $first_month = Carbon::parse($check_schedule->date)->addMonth(1)->addDays(10)->setTime(0,0,0);
            $first_month = Carbon::parse($check_schedule->date)->setTime(0,0,0);


            for ($i=0;$i<3;$i++){
                if($i==0){
                    $trans[] = [
                        'students_id' => $studentId,
                        'class_transactions_id' => $class_id,
                        'transaction_date' => $first_month,
                        'payment_status' => 'Unpaid',
                        'discount' => 0,
                        'price' => $get_class_price->class_price,
                        'desc' => '-',
                        'transaction_quota' => $quota,
                    ];
                } else {
                    $trans[] = [
                        'students_id' => $studentId,
                        'class_transactions_id' => $class_id,
                        'transaction_date' => Carbon::parse($check_schedule->date)->day + 10 > 30 ? Carbon::parse($check_schedule->date)->addMonth($i)->setDay(10) : Carbon::parse($check_schedule->date)->addMonth($i)->setDay(10),
                        'payment_status' => 'Unpaid',
                        'discount' => 0,
                        'price' => $get_class_price->class_price,
                        'desc' => '-',
                        'transaction_quota' => $quota,
                    ];
                }
            }
            DB::table('transactions')->insert($trans);
        }

        $mappingStudent = new MappingClassChild();
        $mappingStudent->student_id = $studentId;
        $mappingStudent->class_id = $class_id;
        $mappingStudent->Save();

        return redirect()->route("adminStudentDetail", ['studentId' => $studentId])->with('msg','Success Add Student into Class');
    }

    public function adminStudentViewSorting($value,$type){
        $students = DB::table('students')
            ->leftJoin('rekenings','students.bank_rek','rekenings.bank_rek')
            ->join('banks','banks.id','rekenings.banks_id')
            ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')
            ->orderBy($value,$type)
            ->paginate(5);
        $sort = $type == 'asc' ? 'asc':'desc';
        return view('admin.student.adminStudentView',compact('students','sort'));
    }

    public function adminStudentFormSubmit(Request $req){
        $rules = [
            'inputLongName' => 'required',
            'inputNickName' => 'required',
            'inputParentName' => 'required',
            'inputCity' => 'required',
            'inputEmail' => 'required|email:filter',
            'inputDate_of_Birth' => 'required|date|before:tomorrow',
            'inputAddress' => 'required',
            'inputPhone1' => 'required|numeric|digits_between:10,12',
            'inputWhatsapp' => 'required|numeric|digits_between:10,12',
            // 'inputRekening' => 'required|numeric|digits_between:10,15',
            'inputPostalCode' => 'required|numeric|min_digits:5',
            // 'inputNis' => 'required|numeric|min_digits:7',
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        if(@$req->inputBankName){
            $bankID = Banks::updateOrCreate([
                'bank_name' => $req->inputBankName
            ]);
        }

        $rekening = new Rekenings();
        $rekening->bank_rek = $req->inputRekening;
        $rekening->nama_pengirim = $req->inputNamaPengirim;
        $rekening->banks_id = is_null($bankID) ? null : $bankID->id;
        $rekening->save();

        $student = new Student();
        $student->nis = $req->inputNis;
        $student->LongName = $req->inputLongName;
        $student->ShortName = $req->inputNickName;
        $student->Email = $req->inputEmail;
        $student->Dob = $req->inputDate_of_Birth;
        $student->Address  = $req->inputAddress;
        $student->nama_orang_tua = $req->inputParentName;
        $student->bank_rek = $req->inputRekening;
        $student->City = $req->inputCity;
        $student->kode_pos = $req->inputPostalCode;
        $student->Phone1 = $req->inputPhone1;
        $student->Phone2 = $req->inputPhone2;
        $student->Whatsapp = $req->inputWhatsapp ;
        $student->Instagram = $req->inputInstagram ?  '@'.$req->inputInstagram : '-';
        $student->Line = $req->inputInstagram ?  $req->inputLine : '-';
        $student->Status = 'aktif';
        $student->EnrollDate  = Carbon::now();
        $student->Quota  = 0;
        $student->is_new  = 0;

        $student->save();

        return redirect()->route('adminStudentView')->with('msg','Success Create Data Student');
    }

    public function active(){
        $students = DB::table('students')
            ->join('rekenings','students.bank_rek','rekenings.bank_rek')
            ->join('banks','banks.id','rekenings.banks_id')
            ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')
            ->where('students.status','=','aktif')
            ->paginate(5);
        return view('admin.student.adminStudentView',compact('students'));
    }

    public function nonActive(){
        $students = DB::table('students')
            ->join('rekenings','students.bank_rek','rekenings.bank_rek')
            ->join('banks','banks.id','rekenings.banks_id')
            ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')
            ->where('students.status','=','non-aktif')
            ->paginate(5);
        return view('admin.student.adminStudentView',compact('students'));
    }

    public function search(Request $req){

        if($req->search >= 1 && $req->search <= 25){
            $curr = Carbon::now()->year;
            $students = DB::table('students')
                ->join('rekenings','students.bank_rek','rekenings.bank_rek')
                ->join('banks','banks.id','rekenings.banks_id')
                ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')
                ->WhereYear('students.Dob',"=",$curr - $req->search)
                ->paginate(5);
        } else {
            $students = DB::table('students')
                ->join('rekenings','students.bank_rek','rekenings.bank_rek')
                ->join('banks','banks.id','rekenings.banks_id')
                ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Address as alamat,
                students.Phone1 as phone,
                students.Email as email,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')
                ->where('students.LongName',"LIKE","%$req->search%")
                ->orWhere('students.ShortName',"LIKE","%$req->search%")
                ->orWhere('students.Instagram',"LIKE","%$req->search%")
                ->orWhere('students.Phone1',"LIKE","%$req->search%")
                ->orWhere('students.Phone2',"LIKE","%$req->search%")
                ->orWhere('students.bank_rek',"LIKE","%$req->search%")
                ->orWhere('students.nama_orang_tua',"LIKE","%$req->search%")
                ->orWhere('students.Address',"LIKE","%$req->search%")
                ->paginate(5);
        }

        return view('admin.student.adminStudentView',compact('students'));
    }

    public function ChangeNonactive(Student $student,Request $req){
        $change = Student::find($student->id);
        if($req->stats == 'Active'){
            $change->Status = 'aktif';
        } else if($req->stats == 'Inactive') {
            $change->Status = 'non-aktif';
        } else {
            $change->Status = 'trial';
        }
        $change->save();
        return redirect()->back()->with('msg','Success Update Student Status');
    }

    public function deleteStudent($studentId){
        $change = Student::find($studentId);
        $change->delete();
        return redirect()->route("adminStudentView")->with('msg','Success Delete Data Student');
    }

    public function detailStudent($studentId){
        $detail = DB::table('students')
            ->leftJoin('rekenings','students.bank_rek','rekenings.bank_rek')
            ->leftJoin('banks','banks.id','rekenings.banks_id')
            ->selectRaw('
                students.id as id,
                students.nis as nis,
                students.Status as status,
                students.LongName as LongName,
                students.ShortName as ShortName,
                students.Dob as dob,
                students.EnrollDate as EnrollDate,
                students.nama_orang_tua as nama_orang_tua,
                students.Address as Address,
                students.City as City,
                students.kode_pos as kode_pos,
                students.Phone1 as Phone1,
                students.Phone2 as Phone2,
                students.Whatsapp as Whatsapp,
                students.Instagram as Instagram,
                students.Line as Line,
                students.Email as Email,
                students.Quota as Quota,
                students.MaxQuota,
                students.Status as Status,
                students.is_new,
                students.age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')->where('students.id', "=", $studentId)->first();

        $courses_taken = DB::table('mapping_class_children')
            ->join('class_transactions', 'mapping_class_children.class_id', 'class_transactions.id')
            ->join('class_types', 'class_transactions.class_type_id', 'class_types.id')
            ->where('mapping_class_children.student_id',"LIKE",$studentId)
            ->groupBy('class_transactions.class_type_id')
            ->get();

            $transactions = Transaction::join('students','students.id','transactions.students_id')
            ->leftjoin('class_transactions','class_transactions.id','transactions.class_transactions_id')
            ->leftjoin('class_types','class_transactions.class_type_id','class_types.id')
            ->selectRaw('
                transactions.id,
                transactions.transaction_date,
                transactions.transaction_payment,
                transactions.payment_status,
                transactions.price as price,
                class_types.class_name,
                transactions.discount,
                transactions.transaction_quota
            ')
            ->where('students.id',$studentId)
            ->orderBy('transactions.id','desc')
            ->get();

        $detail = compact('detail');
        $detail['courses_taken'] = $courses_taken;
        $detail['transactions'] = $transactions;

        return view('admin.student.adminStudentDetail', $detail);
    }

    public function update(Request $request)
    {
        $rules = [
            'LongName' => 'required',
            'nama_orang_tua' => 'required',
            'city' => 'required',
            'Email' => 'required|email:filter',
            'dob' => 'required|date|before:tomorrow',
            'Address' => 'required',
            'Phone1' => 'required|numeric|digits_between:10,12',
            // 'Phone2' => 'required|numeric|digits_between:10,12',
            'Whatsapp' => 'required|numeric|digits_between:10,12',
//            'rek' => 'required|numeric|digits_between:10,15',
            'kode_pos' => 'required|numeric|min_digits:5',
            // 'nis' => 'required|numeric|min_digits:10',
            'Quota' => 'required|numeric',
            'is_new' => 'required',
            'status' => 'required',
        ];

        $validate = Validator::make($request->all(), $rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $student = Student::find($request->id);
        $student->nis = $request->nis;
        $student->LongName = $request->LongName;
        $student->ShortName = $request->ShortName;
        $student->Email = $request->Email;
        $student->Dob = $request->dob;
        $student->Address = $request->Address;
        $student->nama_orang_tua = $request->nama_orang_tua;
        $student->City = $request->city;
        $student->kode_pos = $request->kode_pos;
        $student->Phone1 = $request->Phone1;
        $student->Phone2 = $request->Phone2;
        $student->Whatsapp = $request->Whatsapp;
        $student->Instagram = $request->Instagram;
        $student->Line = $request->Line;
        $student->EnrollDate = $request->EnrollDate;
        $student->Quota = $request->Quota;
        $student->MaxQuota = $request->MaxQuota;
        $student->Status = $request->status;
        if($request->is_new == 'no' || $request->is_new == 'No' || $request->is_new == 'NO'){
            $student->is_new = 0;
        } else {
            $student->is_new = 1;
        }


        $student->save();

        return to_route('adminStudentView')->with('msg','Success Update Data Student');;
    }
}
