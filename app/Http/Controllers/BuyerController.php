<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\ReportStock;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BuyerController extends Controller
{
    public function index(Request $req){
        if(Auth::check()){
            Session::flush();
            Auth::logout();
        }

        $key = $req->search;
        if(!is_null($key)){
            $stocks = Stock::where('name','like',"%$key%")->orderBy('id','desc')->paginate(5);
        } else {
            $stocks = Stock::orderBy('id','desc')->paginate(5);
        }
        $sort = 'asc';
        return view('buyer.index',compact('stocks','sort'));
    }

    public function sorting($value,$sort){
        $stocks = Stock::orderBy($value,$sort)->paginate(5);
        $sort = $sort == 'asc' ? 'desc' : 'asc';
        return view('buyer.index',compact('stocks','sort'));
    }

    public function buyingPage($id){
        $stock = Stock::find($id);
        return view('buyer.buy',compact('stock'));
    }

    public function buying(Request $req,$id){
        $rules = [
            'name' => 'required',
            'qty' => 'required|numeric|min:1'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        DB::table('buyers')->insert([
            'name' => $req->name,
            'stock_id' => $id,
            'qty' => $req->qty,
            'created_at' => now()->setTimezone('GMT+7')->toDateString(),
        ]);

        $stock = Stock::find($id);
        $find = ReportStock::where('stock_id',$stock->id)->first();
        if(!is_null($find)){
            DB::table('report_stocks')->insert([
                'stock_id' => $find->stock_id,
                'out' => $req->qty,
                'report_date' => now()->setTimezone('GMT+7')->toDateString(),
                'first_qty' => $find->first_qty,
            ]);
        } else {
            DB::table('report_stocks')->insert([
                'stock_id' => $stock->id,
                'out' => $req->qty,
                'report_date' => now()->setTimezone('GMT+7')->toDateString(),
                'first_qty' => $stock->quantity
            ]);
        }

        DB::table('stocks')->where('id',$stock->id)->update([
            'quantity' =>  $stock->quantity - $req->qty,
        ]);

        return to_route('buyer')->with(['msg' => 'Thank You']);
    }
}
