<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordEmail;
use App\Models\ForgotPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function index(){
        return view('forgot-password.index');
    }

    public function checkEmail(Request $req){
        $rule = [
            'email' => 'required|email'
        ];
        $validate = Validator::make($req->all(),$rule);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }
        $user = User::where('email',$req->email)->first();
        if(is_null($user)){
            return redirect()->back()->withErrors(['msg' => "Email Doesn't Exists"]);
        }
        $token = bin2hex(random_bytes(32));

        ForgotPassword::updateOrCreate(
            [
            'user_id' => $user->id,
            'token' => $token,
            'expired_at' => now()->setTimezone('GMT+7')->addMinute(30)
            ]
        );

        Mail::to($user->email)->send(new ForgotPasswordEmail($token));
        return to_route('login')->with('msg','Please Check Your Email');
    }

    public function resetPasswordPage($token){
        $data = ForgotPassword::where('token',$token)->firstOrFail();
        if(now() > $data->expired_at){
            return to_route('expired-page');
        }
        return view('forgot-password.reset-password',compact('data'));
    }

    public function resetPassword(Request $req,$token){
        $rule = [
            'new_password' => 'required|confirmed|string|min:5'
        ];
        $validate = Validator::make($req->all(),$rule);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }
        $check = ForgotPassword::where('token',$token)->firstOrFail();
        if(now() > $check->expired_at){
            return to_route('expired-page');
        }
        $check->User->password = bcrypt($req->new_password_confirmation);
        $check->User->save();
        return to_route('login')->with('msg','Success Change Password');
    }
}
