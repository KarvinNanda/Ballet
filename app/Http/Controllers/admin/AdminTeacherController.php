<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminTeacherController extends Controller
{
    public function adminTeacherView(){
        $teachers = User::where('role','teacher')->simplePaginate(5);
        return view('admin.teacher.adminTeacherView',compact('teachers'));
    }

    public function adminTeacherForm(){
        return view('admin.teacher.teacherForm');
    }

    public function search(Request $req){
        $teachers = User::where('role','teacher')
            ->where('name','like',"%$req->search%")
            ->simplePaginate(5);
        return view('admin.teacher.adminTeacherView',compact('teachers'));
    }

    public function delete(User $teacher){
        $delete = User::find($teacher->id);
        $delete->delete();
        return redirect()->back();
    }

    public function deleteTeacher(Request $req){
        $teacher = User::where('id','=',$req->id);
        $teacher->delete();
        return redirect(route('admin.teacher.adminTeacherView'));
    }

    public function detailTeacher(User $teacher){
        $detail = User::find($teacher->id);
        return view('admin.teacher.adminTeacherDetail',compact('detail'));
    }

    public function adminTeacherFormSubmit(Request $req){
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

        Mail::to($user->email)->send(new SendingEmail($credential));
        return redirect()->route("adminTeacherView");
    }
}
