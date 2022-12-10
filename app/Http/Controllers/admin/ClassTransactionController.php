<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

class ClassTransactionController extends Controller
{
    public function adminPage()
    {
        return view('admin');
    }

    public function viewClass(){
        return view('classView');
    }

}
