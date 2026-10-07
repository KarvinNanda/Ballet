<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Models\Rules;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HeadRuleController extends Controller
{
    /** rules.content is TEXT; this keeps one rule readable in the Add Student dialog. */
    private const MAX_CONTENT_LENGTH = 10000;

    public function index(){
        $rules = DB::table('rules')->paginate(5);
        return view('head.rule.index',compact('rules'));
    }

    public function insertPage(){
        return view('head.rule.insert');
    }

    public function insert(Request $req, HtmlSanitizer $sanitizer)
    {
        $data = $req->validate([
            'inputLanguage' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $content = $sanitizer->clean($data['content']);
        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            return back()->withErrors(['content' => 'Isi rule terlalu panjang (maksimal 10.000 karakter, termasuk format).'])->withInput();
        }

        $rule = new Rules();
        $rule->lang = $data['inputLanguage'];
        $rule->content = $content;
        $rule->save();

        return redirect()->route('Rules')->with('msg', 'Success Create Data Rules');
    }

    public function delete(Rules $rules){
            $delete = Rules::find($rules->id);
            $delete->delete();
            return redirect()->back()->with('msg','Success Delete Data Rules');
    }

    public function updatePage(Rules $rules){
        return view('head.rule.update',compact('rules'));
    }

    public function update(Rules $rules, Request $req, HtmlSanitizer $sanitizer)
    {
        $data = $req->validate([
            'inputLanguage' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $content = $sanitizer->clean($data['content']);
        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            return back()->withErrors(['content' => 'Isi rule terlalu panjang (maksimal 10.000 karakter, termasuk format).'])->withInput();
        }

        $rule = $rules;
        $rule->lang = $data['inputLanguage'];
        $rule->content = $content;
        $rule->save();

        return redirect()->route('Rules')->with('msg', 'Success Update Data Rules');
    }
}
