<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\Buyer\BuyRequest;
use App\Models\Stock;
use App\Support\InsufficientStock;
use App\Support\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyerController extends Controller
{
    public function index(SearchRequest $req){
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
        [$value, $sort] = $this->sortOrFail($value, $sort, ['name', 'quantity', 'size']);
        $stocks = Stock::orderBy($value,$sort)->paginate(5);
        $sort = $sort == 'asc' ? 'desc' : 'asc';
        return view('buyer.index',compact('stocks','sort'));
    }

    public function buyingPage(Stock $stock){
        $return_url = url()->previous();
        return view('buyer.buy',compact('stock','return_url'));
    }

    public function buying(BuyRequest $req, Stock $stock){
        $qty = (int) $req->validated('qty');

        try {
            DB::transaction(function () use ($req, $stock, $qty) {
                StockMovement::record($stock->id, 'out', $qty);
                DB::table('buyers')->insert([
                    'name' => $req->validated('name'),
                    'stock_id' => $stock->id,
                    'qty' => $qty,
                    'served_by' => $req->user()->name,
                    'created_at' => now()->setTimezone('GMT+7')->toDateString(),
                ]);
            });
        } catch (InsufficientStock) {
            return redirect()->back()->withInput()->with('error', 'Quantity is Exceed Stock');
        }

        return $this->backTo($req->return_url)->with(['msg' => 'Thank You']);
    }
}
