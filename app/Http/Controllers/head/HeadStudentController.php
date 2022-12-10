<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\ClassTransaction;
use App\Models\Rekenings;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HeadStudentController extends Controller
{

    public function index(){
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
            ->simplePaginate(5);
        return view('head.student.index',compact('students'));
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
                ->simplePaginate(5);
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
                ->simplePaginate(5);
        }

        return view('head.student.index',compact('students'));
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
            ->simplePaginate(5);
        return view('head.student.index',compact('students'));
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
            'inputBankName' => 'required',
            'inputSenderName' => 'required',
            'inputCity' => 'required',
            'inputInstagram' => 'required',
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
        $rekening->nama_pengirim = $req->inputSenderName;
        if($req->inputBankName == 'BCA'){
            $rekening->banks_id = 1;
        } else {
            $rekening->banks_id = 2;
        }
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
        $student->Instagram = '@'.$req->inputInstagram ;
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
}
