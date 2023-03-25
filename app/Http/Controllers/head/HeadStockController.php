<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HeadStockController extends Controller
{
    public function index(){
        $stocks = Stock::simplePaginate(5);
        return view('head.stock.index',compact('stocks'));
    }

    public function search(Request $req){
        $stocks = Stock::where('name','like',"%$req->search%")->simplePaginate(5);
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
            return redirect()->back()->withErrors($validate);
        }

        $stock = new Stock();
        $stock->name = $req->inputName;
        $stock->size = $req->inputSize;
        $stock->quantity = $req->inputQty;
        $stock->save();

        return redirect()->route('headStockPage');
    }

    public function updatePage(Stock $stock){
        return view('head.stock.update',compact('stock'));
    }

    public function update(Request $req,Stock $stock){

        $rules = [
            'inputName' => 'required',
            'inputSize' => 'required',
            'inputQty' => 'required|numeric|min:1'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $stock = Stock::find($stock->id);
        $stock->name = $req->inputName;
        $stock->size = $req->inputSize;
        $stock->quantity = $req->inputQty;
        $stock->save();

        return redirect()->route('headStockPage');
    }

    public function delete(Stock $stock){
        $stock = Stock::find($stock->id);
        $stock->delete();
        return redirect()->route('headStockPage');
    }
}
