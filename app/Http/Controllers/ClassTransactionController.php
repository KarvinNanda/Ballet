<?php

namespace App\Http\Controllers;

use App\Models\ClassTransaction;
use Illuminate\Http\Request;

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
