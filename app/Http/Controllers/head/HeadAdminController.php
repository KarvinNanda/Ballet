<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HeadAdminController extends Controller
{
    public function index(){
        $admins = User::where('role','admin')->orderBy('id','desc')->paginate(5);
        return view('head.admin.index',compact('admins'));
    }

    public function insertPage(){
        return view('head.admin.insert');
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
        $user->role = 'admin';
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

        return redirect()->route('headAdminPage')->with('msg','Success Create Admin');
    }

    public function search(Request $req){
        $admins = User::where('name','like',"%$req->search%")->where('role','admin')->paginate(5);
        return view('head.admin.index',compact('admins'));
    }

    public function delete(User $user){
        $user = User::find($user->id);
        $user->delete();
        return redirect()->route('headAdminPage')->with('msg','Success Delete Admin');
    }


}
