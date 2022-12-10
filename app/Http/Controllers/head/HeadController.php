<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HeadController extends Controller
{
    public function index(){
        return view('head.index');
    }
}
