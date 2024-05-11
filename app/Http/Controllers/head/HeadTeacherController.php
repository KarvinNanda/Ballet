<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HeadTeacherController extends Controller
{

    public  function index(){
        $teachers = User::where('role','teacher')->orderBy('id','desc')->paginate(5);
        return view('head.teacher.index',compact('teachers'));
    }

    public function updatePage(User $teacher){
        $return_url = url()->previous();
        return view('head.teacher.update',compact('teacher','return_url'));
    }

    public function update(Request $req,User $teacher){
        $rules = [
            'inputName' => 'required',
            'inputEmail' => 'required|email:filter',
            'inputDate_of_Birth' => 'required|date|before:tomorrow',
            'inputAddress' => 'required',
            'inputBonus' => 'required|numeric',
            'inputPhone' => 'required|numeric|digits_between:10,12'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $user = User::find($teacher->id);
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->percent = $req->inputBonus;
        $user->save();

        return redirect()->to($req->return_url)->with('msg','Success Update Teacher');
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
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $user = new User();
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->role = 'teacher';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->password = bcrypt('ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY'));
        $user->percent = 30;
        $user->save();

        $credential = [
            'email' => $req->inputEmail,
            'password' => 'ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY')
        ];

        Mail::to($user->email)->send(new SendingEmail($credential));

        return redirect()->route('headTeacherPage')->with('msg','Success Create Teacher');
    }

    public function search(Request $req){
        $teachers = User::where('role','teacher')
            ->where('name','like',"%$req->search%")
            ->paginate(5);
        return view('head.teacher.index',compact('teachers'));
    }

    public function delete(User $teacher){
        $delete = User::find($teacher->id);
        $delete->delete();
        return redirect()->back()->with('msg','Success Delete Teacher');
    }



}
