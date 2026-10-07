<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function index(){
        if(Auth::check()){
            return redirect()->back();
        }
        return view('auth.login');
    }

    public function doLogin(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->to('/'.Auth::user()->role);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
