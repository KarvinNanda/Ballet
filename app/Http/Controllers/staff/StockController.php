<?php

namespace App\Http\Controllers\staff;

use App\Support\Like;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Staff\StockRequest;
use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Stock;
use Illuminate\Support\Facades\Gate;

class StockController extends Controller
{
    public function index(SearchRequest $request){
        $sort = 'asc';
        $stocks = $this->filtered($request)->orderBy('id','desc')->paginate(5)->withQueryString();
        return view('staff.stock.index',compact('stocks','sort'));
    }

    public function sort(SearchRequest $request, $column, $direction){
        [$column, $direction] = $this->sortOrFail($column, $direction, ['name', 'quantity', 'size']);
        $stocks = $this->filtered($request)->orderBy($column,$direction)->orderBy('id','desc')->paginate(5)->withQueryString();
        $sort = $direction == 'asc' ? 'desc' : 'asc';
        return view('staff.stock.index',compact('stocks','sort'));
    }

    /** The list filter shared by index() and sort(), so a sort link keeps the search. SearchRequest already dropped junk values. */
    private function filtered(SearchRequest $request){
        return Stock::query()->when(filled($search = $request->query('search')), fn ($q) => $q->where('name', 'like', Like::contains($search)));
    }

    public function create(){
        Gate::authorize('stock.manage');
        return view('staff.stock.insert');
    }

    public function store(StockRequest $req){
        $stock = new Stock();
        $stock->name = $req->inputName;
        $stock->size = $req->inputSize;
        $stock->quantity = $req->inputQty;
        $stock->save();

        return redirect(staff_route('stock.index'))->with('msg','Success Create Stock');
    }

    public function edit(Stock $stock){
        Gate::authorize('stock.manage');
        $return_url = url()->previous();
        $buyer = Buyer::where('stock_id',$stock->id)->paginate(5);
        return view('staff.stock.update',compact('stock','buyer','return_url'));
    }

    public function update(StockRequest $req, Stock $stock){
        $stock->name = $req->inputName;
        $stock->size = $req->inputSize;
        $stock->quantity = $req->inputQty;
        $stock->save();

        return $this->backTo($req->return_url)->with('msg','Success Update Stock');
    }

    public function destroy(Stock $stock){
        Gate::authorize('stock.manage');
        $stock->delete();
        return redirect()->back()->with('msg','Success Delete Stock');
    }
}
