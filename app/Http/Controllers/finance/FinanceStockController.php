<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\ReportStock;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinanceStockController extends Controller
{
    public function in(Stock $stock){
        $type = 'in';
        $buyer = Buyer::where('stock_id',$stock->id)->paginate(5);
        return view('finance.in-out',compact('stock','type','buyer'));
    }

    public function financeStock($value,$sort){
        $stocks = Stock::orderBy($value,$sort)->paginate(5);
        $sort = $sort == 'asc' ? 'desc' : 'asc';
        return view('finance.index',compact('stocks','sort'));
    }

    public function out(Stock $stock){
        $type = 'out';
        return view('finance.in-out',compact('stock','type'));
    }

    public function report(Stock $stock,$type,Request $req){
        if(!empty($req->in_out) && $req->in_out != 0){
            $find = ReportStock::where('stock_id',$stock->id)->first();
            if(!is_null($find)){
                DB::table('report_stocks')->insert([
                    'stock_id' => $find->stock_id,
                    $type => $req->in_out,
                    'report_date' => now()->setTimezone('GMT+7')->toDateString(),
                    'first_qty' => $find->first_qty,
                ]);
            } else {
                DB::table('report_stocks')->insert([
                    'stock_id' => $stock->id,
                    $type => $req->in_out,
                    'report_date' => now()->setTimezone('GMT+7')->toDateString(),
                    'first_qty' => $stock->quantity
                ]);
            }

        }
        DB::table('stocks')->where('id',$stock->id)->update([
            'quantity' => $type == 'in' ? $stock->quantity + $req->in_out : $stock->quantity - $req->in_out
        ]);

        return to_route('finance')->with('msg','Success Update Stock');
    }

    public function stock(){
        $data = ReportStock::all()->groupBy('report_date');
        return view('finance.report.stock.index',compact('data'));
    }

    public function printStock(ReportStock $report_stock){
        $report = ReportStock::whereDate('report_date',$report_stock->report_date)
            ->get()
            ->groupBy('stock_id');
        $date = $report_stock->report_date;

        $pdf = Pdf::loadView('finance.report.stock.print',compact('report','date'))
            ->stream('Laporan Stock '.Carbon::parse($report_stock->report_date)->format('dmY').'.pdf');
        return $pdf;
    }
}
