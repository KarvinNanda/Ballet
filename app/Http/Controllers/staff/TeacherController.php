<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function index(Request $request){
        $teachers = User::where('role','teacher')
            ->when($request->query('search'), fn ($q, $s) => $q->where('name','like',"%{$s}%"))
            ->orderBy('id','desc')
            ->paginate(5)
            ->withQueryString();
        return view('staff.teacher.index',compact('teachers'));
    }

    public function create(){
        return view('staff.teacher.insert');
    }

    public function store(Request $req){
        $rules = [
            'inputName' => 'required|string|max:255',
            'inputEmail' => 'required|email:filter',
            'inputDate_of_Birth' => 'required|date|before:tomorrow',
            'inputAddress' => 'required|string|max:255',
            'inputPhone' => 'required|numeric|digits_between:10,12'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $user = new User();
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->role = 'teacher';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->password = bcrypt('ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY'));
        $user->percent = 30;
        $user->save();

        $credential = [
            'email' => $req->inputEmail,
            'password' => 'ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY')
        ];

        Mail::to($user->email)->send(new SendingEmail($credential));

        return redirect(staff_route('teacher.index'))->with('msg','Success Create Data Teacher');
    }

    public function edit(User $teacher){
        abort_unless($teacher->role === 'teacher', 404);
        $return_url = url()->previous();
        return view('staff.teacher.update',compact('teacher','return_url'));
    }

    public function update(Request $req,User $teacher){
        abort_unless($teacher->role === 'teacher', 404);
        $rules = [
            'inputName' => 'required|string|max:255',
            'inputEmail' => 'required|email:filter',
            'inputDate_of_Birth' => 'required|date|before:tomorrow',
            'inputAddress' => 'required|string|max:255',
            'inputBonus' => 'required|integer|min:0|max:2000000000',
            'inputPhone' => 'required|numeric|digits_between:10,12'
        ];

        $validate = Validator::make($req->all(),$rules);
        if($validate->fails()){
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $teacher->name = $req->inputName;
        $teacher->address = $req->inputAddress;
        $teacher->dob = $req->inputDate_of_Birth;
        $teacher->email = $req->inputEmail;
        $teacher->phone = $req->inputPhone;
        $teacher->percent = $req->inputBonus;
        $teacher->save();

        return $this->backTo($req->return_url)->with('msg','Success Update Data Teacher');
    }

    public function destroy(User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        // A teacher with an active class must be replaced first; the switch page handles that.
        if ($this->hasActiveClass($teacher)) {
            return redirect(staff_route('teacher.switch', $teacher));
        }

        $teacher->delete();

        return redirect()->back()->with('msg', 'Success Delete Data Teacher');
    }

    public function switch(User $teacher, Request $req)
    {
        abort_unless($teacher->role === 'teacher', 404);

        $keyword = $req->query('search');
        $teachers = User::where('role', 'teacher')
            ->where('id', '!=', $teacher->id)
            ->when($keyword, fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
            ->orderBy('id', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('staff.teacher.switch', compact('teachers', 'teacher'));
    }

    public function replace(User $teacher, int $replacement)
    {
        abort_unless($teacher->role === 'teacher', 404);
        $new = User::where('role', 'teacher')->whereKeyNot($teacher->id)->findOrFail($replacement);

        DB::table('mapping_class_teachers as mct')
            ->leftJoin('class_transactions as ct','ct.id','mct.class_id')
            ->where('ct.is_freeze','!=',1)
            ->where('mct.user_id',$teacher->id)
            ->update(['mct.user_id' => $new->id]);
        $teacher->delete();

        return redirect(staff_route('teacher.index'))->with('msg','Success Replace & Delete Data Teacher');
    }

    private function hasActiveClass(User $teacher): bool
    {
        return DB::table('mapping_class_teachers as mct')
            ->leftJoin('class_transactions as ct', 'ct.id', 'mct.class_id')
            ->where('ct.is_freeze', '!=', 1)
            ->where('mct.user_id', $teacher->id)
            ->exists();
    }
}
