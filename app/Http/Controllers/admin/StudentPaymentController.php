<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPaymentController extends Controller
{
    public function viewStudentDetail(){
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
        return view('admin.student.adminStudentView',compact('students'));
    }
}
