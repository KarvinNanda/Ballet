<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Rules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HeadRuleController extends Controller
{
    public function index(){
        $rules = DB::table('rules')->paginate(5);
        return view('head.rule.index',compact('rules'));
    }

    public function insertPage(){
        return view('head.rule.insert');
    }

    public function insert(Request $req){
        $rules = [
            'inputLanguage' => 'required',
            'content' => 'required',
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $rule = new Rules();
        $rule->lang=$req->inputLanguage;
        $rule->content=$req->content;
        $rule->save();
        return redirect()->route('Rules')->with('msg','Success Create Data Rules');
    }

    public function delete(Rules $rules){
            $delete = Rules::find($rules->id);
            $delete->delete();
            return redirect()->back()->with('msg','Success Delete Data Rules');
    }

    public function updatePage(Rules $rules){
        return view('head.rule.update',compact('rules'));
    }

    public function update(Rules $rules,Request $req){
        $rul = [
            'inputLanguage' => 'required',
            'content' => 'required',
        ];

        $validate = Validator::make($req->all(),$rul);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $rule = Rules::find($rules->id);
        $rule->lang=$req->inputLanguage;
        $rule->content=$req->content;
        $rule->save();
        return redirect()->route('Rules')->with('msg','Success Create Data Rules');
    }
}
