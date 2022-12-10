<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function changeProfilePage(){
        $user = Auth::user();
        return view('profile.profile',compact('user'));
    }

    public function changeProfile(Request $req,User $user){
        $rules = [
            'email' => 'required|email:filter',
            'address' => 'required',
            'phone' => 'required|numeric|digits_between:10,12'
        ];

        $validate = Validator::make($req->all(),$rules);


        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $change = User::find($user->id);
        $change->address = $req->address;
        $change->email = $req->email;
        $change->phone = $req->phone;
        $change->save();

        return redirect()->route('change-profile-page');
    }

    public function changePasswordPage(){
        $user = Auth::user();
        return view('profile.password',compact('user'));
    }

    public function changePassword(Request $req,User $user){
        $rules = [
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password'
        ];

        $validate = Validator::make($req->all(),$rules);

        $active=false;

        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $change = User::find($user->id);
        $change->password = bcrypt($req->confirm_password);
        $change->save();

        return redirect()->route('change-password-page');
    }
}
