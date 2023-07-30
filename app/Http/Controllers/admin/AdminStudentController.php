<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
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

    public function adminStudentView(){
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
        return view('admin.student.adminStudentView',compact('students'));
    }

    public function adminStudentViewSorting($value){
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
            ->orderBy($value)
            ->simplePaginate(5);
        return view('admin.student.adminStudentView',compact('students'));
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

        return redirect()->route('adminStudentView');
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
            ->simplePaginate(5);
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

        return view('admin.student.adminStudentView',compact('students'));
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

    public function deleteStudent($studentId){
        $change = Student::find($studentId);
        $change->delete();
        return redirect()->route("adminStudentView");
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



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        //
    }
}
