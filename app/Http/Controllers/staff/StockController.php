<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function index(Request $request){
        $sort = 'asc';
        $search = $request->search;
        if(is_null($search)) $stocks = Stock::orderBy('id','desc')->paginate(5);
        else  $stocks = Stock::where('name','like',"%$search%")->orderBy('id','desc')->paginate(5);
        return view('staff.stock.index',compact('stocks','sort'));
    }

    public function sort($column,$direction){
        [$column, $direction] = $this->sortOrFail($column, $direction, ['name', 'quantity', 'size']);
        $stocks = Stock::orderBy($column,$direction)->paginate(5);
        $sort = $direction == 'asc' ? 'desc' : 'asc';
        return view('staff.stock.index',compact('stocks','sort'));
    }

    public function create(){
        Gate::authorize('stock.manage');
        return view('staff.stock.insert');
    }

    public function store(Request $req){
        Gate::authorize('stock.manage');
        $rules = [
            'inputName' => 'required|string|max:255',
            'inputSize' => 'required|string|max:255',
            'inputQty' => 'required|integer|min:1|max:2000000000'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

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

    public function update(Request $req,Stock $stock){
        Gate::authorize('stock.manage');

        $rules = [
            'inputName' => 'required|string|max:255',
            'inputSize' => 'required|string|max:255',
            'inputQty' => 'required|integer|min:1|max:2000000000'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

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
