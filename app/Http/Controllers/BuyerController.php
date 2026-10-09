<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\Buyer\BuyRequest;
use App\Models\Stock;
use App\Support\InsufficientStock;
use App\Support\StockMovement;
use Illuminate\Support\Facades\DB;

class BuyerController extends Controller
{
    public function index(SearchRequest $req){
        $sort = 'asc';
        // SearchRequest has already dropped junk search values.
        $stocks = Stock::search($req->query('search'))->orderBy('id','desc')->paginate(5)->withQueryString();
        return view('buyer.index',compact('stocks','sort'));
    }

    public function sorting(SearchRequest $req, $value, $sort){
        [$value, $sort] = $this->sortOrFail($value, $sort, ['name', 'quantity', 'size']);
        // Same search filter as index(), so the sort links keep it.
        $stocks = Stock::search($req->query('search'))->orderBy($value,$sort)->orderBy('id','desc')->paginate(5)->withQueryString();
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
        } catch (InsufficientStock $e) {
            // On the quantity field: the page's max is the stock at page load, which may have changed since.
            return redirect()->back()->withErrors(['qty' => $e->getMessage()])->withInput();
        }

        return $this->backTo($req->return_url)->with(['msg' => 'Thank You']);
    }
}
