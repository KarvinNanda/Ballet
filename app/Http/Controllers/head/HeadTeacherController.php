<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\DetailAbsen;
use App\Models\HeaderAbsen;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HeadTeacherController extends Controller
{

    public  function index(){
        $teachers = User::where('role','teacher')->simplePaginate(5);
        return view('head.teacher.index',compact('teachers'));
    }

    public function insertPage(){
        return view('head.teacher.insert');
    }

    public function insert(Request $req){
        $rules = [
            'inputName' => 'required',
            'inputEmail' => 'required|email:filter',
            'inputDate_of_Birth' => 'required|date|before:tomorrow',
            'inputAddress' => 'required',
            'inputPhone' => 'required|numeric|digits_between:10,12'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $user = new User();
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->role = 'teacher';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->password = bcrypt('ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY'));
        $user->save();

        $credential = [
            'email' => $req->inputEmail,
            'password' => 'ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY')
        ];

        Mail::to('bsena692@gmail.com')->send(new SendingEmail($credential));

        return redirect()->route('headTeacherPage');
    }

    public function search(Request $req){
        $teachers = User::where('role','teacher')
            ->where('name','like',"%$req->search%")
            ->simplePaginate(5);
        return view('head.teacher.index',compact('teachers'));
    }

    public function delete(User $teacher){
        $delete = User::find($teacher->id);
        $delete->delete();
        return redirect()->back();
    }



}
