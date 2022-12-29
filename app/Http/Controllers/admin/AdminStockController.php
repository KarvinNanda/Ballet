<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;

class AdminStockController extends Controller
{
    public function index(){
        $stocks = Stock::simplePaginate(5);
        return view('admin.stock.index',compact('stocks'));
    }
}
