<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminTeacherController extends Controller
{
    public function adminTeacherView(){
        $teachers = User::where('role','teacher')->orderBy('id','desc')->paginate(5);
        return view('admin.teacher.adminTeacherView',compact('teachers'));
    }

    public function adminTeacherForm(){
        return view('admin.teacher.teacherForm');
    }

    public function search(Request $req){
        $teachers = User::where('role','teacher')
            ->where('name','like',"%$req->search%")
            ->paginate(5);
        return view('admin.teacher.adminTeacherView',compact('teachers'));
    }

    public function delete(User $teacher,Request $req){
        $checkTeacherOnClass = DB::table('mapping_class_teachers as mct')
                                    ->leftJoin('class_transactions as ct','ct.id','mct.class_id')
                                    ->where('ct.is_freeze','!=',1)
                                    ->where('mct.user_id',$teacher->id)
                                    ->first();
        if(is_null($checkTeacherOnClass)){
            $delete = User::find($teacher->id);
            $delete->delete();
            return redirect()->back()->with('msg','Success Delete Data Teacher');
        } else {
            $keyword=$req->search;
            $teachers = User::where('role','teacher')
                            ->where('id','!=',$teacher->id)
                            ->where(function($q) use ($keyword){
                                if(!is_null($keyword)){
                                    $q->where('name','like',"%$keyword%");
                                } 
                            })
                            ->orderBy('id','desc')
                            ->paginate(5);
            return view('admin.teacher.teacherSwitch',compact('teachers','teacher'));
        }
    }

    public function replace(User $teacher,$replaceTeacherID){
        DB::table('mapping_class_teachers as mct')
                                    ->leftJoin('class_transactions as ct','ct.id','mct.class_id')
                                    ->where('ct.is_freeze','!=',1)
                                    ->where('mct.user_id',$teacher->id)
                                    ->update([
                                        'mct.user_id' => $replaceTeacherID
                                    ]);
        $delete = User::find($teacher->id);
        $delete->delete();                       
        return redirect()->route("adminTeacherView")->with('msg','Success Replace & Delete Data Teacher');
    }

    public function updatePage(User $teacher){
        $return_url = url()->previous();
        return view('admin.teacher.update',compact('teacher','return_url'));
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

        return redirect()->to($req->return_url)->with('msg','Success Update Data Teacher');
    }

    public function deleteTeacher(Request $req){
        $teacher = User::where('id','=',$req->id);
        $teacher->delete();
        return redirect()->back()->with('msg','Success Delete Data Teacher');
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

        return redirect()->route("adminTeacherView")->with('msg','Success Create Data Teacher');
    }
}
