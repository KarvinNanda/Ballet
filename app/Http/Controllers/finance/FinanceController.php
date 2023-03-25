<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\Rekenings;
use App\Models\ReportStock;
use App\Models\Stock;

class FinanceController extends Controller
{
    public function index(){
        $stocks = Stock::simplePaginate(5);
        return view('finance.index',compact('stocks'));;
    }
}
