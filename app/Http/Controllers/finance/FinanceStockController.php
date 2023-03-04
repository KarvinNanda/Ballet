<?php

namespace App\Http\Controllers\finance;

use App\Http\Controllers\Controller;
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
        return view('finance.in-out',compact('stock','type'));
    }

    public function out(Stock $stock){
        $type = 'out';
        return view('finance.in-out',compact('stock','type'));
    }

    public function report(Stock $stock,$type,Request $req){
        if(!empty($req->in_out) && $req->in_out != 0){
            DB::table('report_stocks')->insert([
                'stock_id' => $stock->id,
                $type => $req->in_out,
                'report_date' => now()->setTimezone('GMT+7')->toDateString()
            ]);
        }

        return to_route('finance');
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
        $in=$out=0;
        foreach($report as $r){
            foreach($r as $i){
                $in+=$i->in;
                $out+=$i->out;
            }
            Stock::where('name',$r[0]->stock->name)->update(['quantity' => $r[0]->stock->quantity + ($in-$out)]);
            $in=$out=0;
        }

        DB::table('report_stocks')->whereDate('report_date',$date)->delete();

        $pdf = Pdf::loadView('finance.report.stock.print',compact('report','date'))
            ->download('Laporan Stock '.Carbon::parse($report_stock->report_date)->format('dmY').'.pdf');
        return $pdf;
    }
}
