<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HeadStockController extends Controller
{
    public function index(Request $request){
        $sort = 'asc';
        $search = $request->search;
        if(is_null($search)) $stocks = Stock::orderBy('id','desc')->paginate(5);
        else  $stocks = Stock::where('name','like',"%$search%")->orderBy('id','desc')->paginate(5);
        return view('head.stock.index',compact('stocks','sort'));
    }

    public function sorting($value,$sort){
        $stocks = Stock::orderBy($value,$sort)->paginate(5);
        $sort = $sort == 'asc' ? 'desc' : 'asc';
        return view('head.stock.index',compact('stocks','sort'));
    }

    public function search(Request $req){
        $stocks = Stock::where('name','like',"%$req->search%")->paginate(5);
        return view('head.stock.index',compact('stocks'));
    }

    public function insertPage(){
        return view('head.stock.insert');
    }

    public function insert(Request $req){
        $rules = [
            'inputName' => 'required',
            'inputSize' => 'required',
            'inputQty' => 'required|numeric|min:1'
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

        return redirect()->route('headStockPage')->with('msg','Success Create Stock');
    }

    public function updatePage(Stock $stock){
        $return_url = url()->previous();
        $buyer = Buyer::where('stock_id',$stock->id)->paginate(5);
        return view('head.stock.update',compact('stock','buyer','return_url'));
    }

    public function update(Request $req,Stock $stock){

        $rules = [
            'inputName' => 'required',
            'inputSize' => 'required',
            'inputQty' => 'required|numeric|min:1'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $stock = Stock::find($stock->id);
        $stock->name = $req->inputName;
        $stock->size = $req->inputSize;
        $stock->quantity = $req->inputQty;
        $stock->save();

        return redirect()->to($req->return_url)->with('msg','Success Update Stock');
    }

    public function delete(Stock $stock){
        $stock = Stock::find($stock->id);
        $stock->delete();
        return redirect()->back()->with('msg','Success Delete Stock');
    }
}
