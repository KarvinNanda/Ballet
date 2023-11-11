<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;

class AdminStockController extends Controller
{
    public function index(Request $request){
        $sort = 'asc';
        $search = $request->search;
        if(is_null($search)) $stocks = Stock::orderBy('id','desc')->paginate(5);
        else  $stocks = Stock::where('name','like',"%$search%")->orderBy('id','desc')->paginate(5);
        return view('admin.stock.index',compact('stocks','sort'));
    }

    public function adminStock($value,$sort){
        $stocks = Stock::orderBy($value,$sort)->paginate(5);
        $sort = $sort == 'asc' ? 'desc' : 'asc';
        return view('admin.stock.index',compact('stocks','sort'));
    }
}
