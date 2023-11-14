<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\Rekenings;
use App\Models\Student;
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
                ->paginate(5);
        }
        return view('admin.student.adminStudentView',compact('students','sort'));
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
            'inputPhone2' => 'required|numeric|digits_between:10,12',
            'inputWhatsapp' => 'required|numeric|digits_between:10,12',
            'inputRekening' => 'required|numeric|digits_between:10,15',
            'inputPostalCode' => 'required|numeric|min_digits:5',
            'inputNis' => 'required|numeric|min_digits:10',
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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
                students.is_new,
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
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

        $detail = compact('detail');
        $detail['courses_taken'] = $courses_taken;

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
            'Phone2' => 'required|numeric|digits_between:10,12',
            'Whatsapp' => 'required|numeric|digits_between:10,12',
//            'rek' => 'required|numeric|digits_between:10,15',
            'kode_pos' => 'required|numeric|min_digits:5',
            'nis' => 'required|numeric|min_digits:10',
            'Quota' => 'required|numeric',
            'is_new' => 'required',
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
        if($request->is_new == 'no' || $request->is_new == 'No' || $request->is_new == 'NO'){
            $student->is_new = 0;
        } else {
            $student->is_new = 1;
        }


        $student->save();

        return to_route('adminStudentView')->with('msg','Success Update Data Student');;
    }
}
