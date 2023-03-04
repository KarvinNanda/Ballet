<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\Rekenings;
use App\Models\ReportStock;
use App\Models\Stock;

class FinanceController extends Controller
{
    public function index(){
//        $in=$out=0;
//        $i=ReportStock::all()->groupBy('stock_id');
////        dd($i[1][0]->stock);
//        foreach ($i as $ii){
//            foreach ($ii as $iii){
//                $in += $iii->in;
//                $out += $iii->out;
//            }
//            echo "QTY AWAL : ".$ii[0]->stock->quantity."<br>";
//            echo "IN : ".$in."<br>";
//            echo "OUT : ".$out."<br>";
//            echo "QTY AKHIR : ".$ii[0]->stock->quantity + ($in - $out)."<br>";
//            echo "Stock : ".$ii[0]->stock_id."<br> <br>";
//            $in=$out=0;
//        }
        $stocks = Stock::simplePaginate(5);
        return view('finance.index',compact('stocks'));;
    }
}
