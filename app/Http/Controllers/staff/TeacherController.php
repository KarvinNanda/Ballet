<?php

namespace App\Http\Controllers\staff;

use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreTeacherRequest;
use App\Http\Requests\Staff\UpdateTeacherRequest;
use App\Support\AccountInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(SearchRequest $request){
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

    public function store(StoreTeacherRequest $req){
        $user = new User();
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->role = 'teacher';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->percent = 30;
        $user->save();
        AccountInvite::send($user);

        return redirect(staff_route('teacher.index'))->with('msg','Success Create Data Teacher');
    }

    public function edit(User $teacher){
        abort_unless($teacher->role === 'teacher', 404);
        $return_url = url()->previous();
        return view('staff.teacher.update',compact('teacher','return_url'));
    }

    public function update(UpdateTeacherRequest $req,User $teacher){
        abort_unless($teacher->role === 'teacher', 404);
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

    public function switch(User $teacher, SearchRequest $req)
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
