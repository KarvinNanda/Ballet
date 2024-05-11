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
        $return_url = url()->previous();
        $type = 'in';
        $buyer = Buyer::where('stock_id',$stock->id)->paginate(5);
        return view('finance.in-out',compact('stock','type','buyer','return_url'));
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

        return redirect()->to($req->return_url)->with('msg','Success Update Stock');
    }

    public function stock(){
        $data = ReportStock::all()->groupBy('report_date');
        return view('finance.report.stock.index',compact('data'));
    }

    public function printStock(Request $req){
        $start_date = $req->start_date." 00:00:00";
        $end_date = $req->end_date." 23:59:59";
        $report = ReportStock::whereBetween('report_date',[$start_date,$end_date])
            // ->groupBy('stock_id')
            ->selectRaw("
                stock_id,
                first_qty,
                report_date,
                sum(report_stocks.in) as in_qty,
                sum(report_stocks.out) as out_qty
            ")
            ->groupBy('stock_id','first_qty','report_date')
            ->get();
            
        $date = Carbon::parse($start_date)->toDateString() == Carbon::parse($end_date)->toDateString() ? Carbon::parse($start_date)->format('dmY') : Carbon::parse($start_date)->format('dmY')."-".Carbon::parse($end_date)->format('dmY');

        $stock = Stock::all();

        $pdf = Pdf::loadView('finance.report.stock.print',compact('report','start_date','end_date','stock'))
            ->stream('Laporan Stock '.$date.'.pdf');
        return $pdf;
    }
}
