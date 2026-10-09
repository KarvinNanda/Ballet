<?php

namespace App\Http\Controllers\head;

use App\Http\Controllers\Controller;
use App\Http\Requests\Head\SearchAdminRequest;
use App\Http\Requests\Head\StoreAdminRequest;
use App\Http\Requests\Head\UpdateAdminRequest;
use App\Support\AccountInvite;
use App\Models\User;
use Illuminate\Http\Request;

class HeadAdminController extends Controller
{
    public function index(){
        $admins = User::where('role','admin')->orderBy('id','desc')->paginate(5);
        return view('head.admin.index',compact('admins'));
    }

    public function updatePage(User $user){
        abort_unless($user->role === 'admin', 404); // these routes manage admin accounts only
        $return_url = url()->previous();
        return view('head.admin.update',compact('user','return_url'));
    }

    public function update(UpdateAdminRequest $req,User $user){
        abort_unless($user->role === 'admin', 404); // these routes manage admin accounts only
        $user = User::find($user->id);
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->percent = $req->inputBonus;
        $user->save();

        return $this->backTo($req->return_url)->with('msg','Success Update Admin');
    }

    public function insertPage(){
        return view('head.admin.insert');
    }

    public function insert(StoreAdminRequest $req){
        $user = new User();
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->role = 'admin';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->save();
        AccountInvite::send($user);

        return redirect()->route('headAdminPage')->with('msg','Success Create Admin');
    }

    public function search(SearchAdminRequest $req){
        $admins = User::where('name','like',"%$req->search%")->where('role','admin')->paginate(5);
        return view('head.admin.index',compact('admins'));
    }

    public function delete(User $user){
        abort_unless($user->role === 'admin', 404); // these routes manage admin accounts only
        $user = User::find($user->id);
        $user->delete();
        return redirect()->back()->with('msg','Success Delete Admin');
    }


}
