<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HeadFinanceController extends Controller
{
    public function index(){
        $finances = User::where('role','finance')->orderBy('id','desc')->paginate(5);
        return view('head.finance.index',compact('finances'));
    }

    public function insertPage(){
        return view('head.finance.insert');
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
        $user->role = 'finance';
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

        return to_route('headFinancePage')->with('msg','Success Create Finance Data');
    }

    public function search(Request $req){
        $finances = User::where('name','like',"%$req->search%")->where('role','finance')->paginate(5);
        return view('head.finance.index',compact('finances'));
    }

    public function delete(User $user){
        $user = User::find($user->id);
        $user->delete();
        return to_route('headFinancePage')->with('msg','Success Create Finance Data');
    }
}
