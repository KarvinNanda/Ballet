<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Rekenings;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HeadStudentController extends Controller
{
    public function index(){
        $students = DB::table('students')
            ->join('rekenings','students.bank_rek','rekenings.bank_rek')
            ->selectRaw('
                students.id as id,
                students.Status as status,
                students.LongName as name,
                students.Dob as dob,
                students.nama_orang_tua as ortu,
                students.Phone1 as phone,
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim
            ')
            ->simplePaginate(5);
        return view('head.student.index',compact('students'));
    }

    public function deleteStudent($studentId){
        $change = Student::find($studentId);
        $change->delete();
        return redirect()->route("headStudentPage");
    }

    public function sorting($value){
        $students= DB::table('students')
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
            ->orderBy($value)
            ->simplePaginate(5);
        return view('head.student.index',compact('students'));
    }

    public function search(Request $req){

        if($req->search >= 1 && $req->search <= 25){
            $curr = Carbon::now()->year;
            $students = DB::table('students')
                ->join('rekenings','students.bank_rek','rekenings.bank_rek')
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
                ->WhereYear('students.Dob',"=",$curr - $req->search)
                ->simplePaginate(5);
        } else {
            $students = DB::table('students')
                ->join('rekenings','students.bank_rek','rekenings.bank_rek')
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
                ->where('students.LongName',"LIKE","%$req->search%")
                ->orWhere('students.ShortName',"LIKE","%$req->search%")
                ->orWhere('students.Instagram',"LIKE","%$req->search%")
                ->orWhere('students.Phone1',"LIKE","%$req->search%")
                ->orWhere('students.Phone2',"LIKE","%$req->search%")
                ->orWhere('students.bank_rek',"LIKE","%$req->search%")
                ->orWhere('students.nama_orang_tua',"LIKE","%$req->search%")
                ->orWhere('students.Address',"LIKE","%$req->search%")
                ->simplePaginate(5);
        }

        return view('head.student.index',compact('students'));
    }

    public function active(){
        $students = DB::table('students')
            ->join('rekenings','students.bank_rek','rekenings.bank_rek')
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
            ->where('students.status','=','aktif')
            ->simplePaginate(5);
        return view('head.student.index',compact('students'));
    }

    public function nonActive(){
        $students = DB::table('students')
            ->join('rekenings','students.bank_rek','rekenings.bank_rek')
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
            ->where('students.status','=','non-aktif')
            ->simplePaginate(5);
        return view('head.student.index',compact('students'));
    }

    public function insertPage(){
        return view('head.student.insert');
    }

    public function insert(Request $req){
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
            return redirect()->back()->withErrors($validate);
        }

        $rekening = new Rekenings();
        $rekening->bank_rek = $req->inputRekening;
        $rekening->nama_pengirim = '-';
        $rekening->banks_id = null;
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

        $student->save();


        return redirect()->route('headStudentPage');
    }

    public function ChangeNonactive(Student $student){
        $change = Student::find($student->id);
        if($change->Status == 'aktif'){
            $change->Status = 'non-aktif';
        } else {
            $change->Status = 'aktif';
        }
        $change->save();
        return redirect()->back();
    }

    public function detailStudent(Student $student, $id){
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim,
                banks.bank_name as bank
            ')->where('students.id',"LIKE", $id)->first();

            if(is_null($detail)){
            $detail = DB::table('students')
                ->join('rekenings','students.bank_rek','rekenings.bank_rek')
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
                YEAR(CURDATE()) - YEAR(students.Dob) as age,
                rekenings.bank_rek as rek,
                rekenings.nama_pengirim as pengirim
            ')->where('students.LongName',"LIKE",$student->LongName)->first();
        }

        return view('head.student.detail', compact('detail'));
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
            'rek' => 'required|numeric|digits_between:10,15',
            'kode_pos' => 'required|numeric|min_digits:5',
            'nis' => 'required|numeric|min_digits:10',
        ];

        $validate = Validator::make($request->all(), $rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $student = Student::find($request->id);
        $student->nis = $request->nis;
        $student->LongName = $request->LongName;
        $student->ShortName = $request->ShortName;
        $student->Email = $request->Email;
        $student->Dob = $request->dob;
        $student->Address = $request->Address;
        $student->nama_orang_tua = $request->nama_orang_tua;
        // $student->bank = $request->bank;
        // $student->pengirim = $request->pengirim;
        // $student->rek = $request->rek;
        $student->City = $request->city;
        $student->kode_pos = $request->kode_pos;
        $student->Phone1 = $request->Phone1;
        $student->Phone2 = $request->Phone2;
        $student->Whatsapp = $request->Whatsapp;
        $student->Instagram = $request->Instagram;
        $student->Line = $request->Line;
        $student->EnrollDate = $request->EnrollDate;

        $student->save();

        return redirect()->back();
    }
}
