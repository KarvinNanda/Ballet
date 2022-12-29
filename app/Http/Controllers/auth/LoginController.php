<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function index(){
        if(Auth::check()){
            return redirect()->back();
        }
        return view('auth.login');
    }

    public function doLogin(Request $req){
        $rules = [
            'email' => 'required',
            'password' => 'required'
        ];

        $validate = Validator::make($req->all(),$rules);

        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $credential = [
          'email' => $req->email,
          'password' => $req->password
        ];

        if(Auth::attempt($credential,$req->remember)){
            $user = User::find(Auth::id());
            if($user->role == 'head'){
                return redirect()->to('/head');
            } else if ($user->role == 'admin'){
                return redirect()->to('/admin');
            } else{
                return redirect()->to('/teacher');
            }
        } else {
            return redirect()->back()->withErrors(['msg' => 'User Not Found']);
        }
    }

    public function logout(){
        Session::flush();
        Auth::logout();
        return redirect()->to('/login');
    }
}
