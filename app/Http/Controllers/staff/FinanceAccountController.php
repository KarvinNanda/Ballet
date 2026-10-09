<?php

namespace App\Http\Controllers\staff;

use App\Support\Like;
use App\Http\Requests\SearchRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreFinanceAccountRequest;
use App\Http\Requests\Staff\UpdateFinanceAccountRequest;
use App\Support\AccountInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FinanceAccountController extends Controller
{
    public function index(SearchRequest $request){
        $finances = User::where('role','finance')
            ->when(filled($s = $request->query('search')), fn ($q) => $q->where('name','like',Like::contains($s)))
            ->orderBy('id','desc')
            ->paginate(5)
            ->withQueryString();
        return view('staff.finance.index',compact('finances'));
    }

    public function create(){
        return view('staff.finance.insert');
    }

    public function store(StoreFinanceAccountRequest $req){
        $user = new User();
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->role = 'finance';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->save();
        AccountInvite::send($user);

        return redirect(staff_route('finance.index'))->with('msg','Success Create Finance Data');
    }

    public function edit(User $user){
        Gate::authorize('finance.manage');
        abort_unless($user->role === 'finance', 404);
        $return_url = url()->previous();
        return view('staff.finance.update',compact('user','return_url'));
    }

    public function update(UpdateFinanceAccountRequest $req,User $user){
        abort_unless($user->role === 'finance', 404);
        $user->name = $req->inputName;
        $user->address = $req->inputAddress;
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->percent = $req->inputBonus;
        $user->save();

        return $this->backTo($req->return_url)->with('msg','Success Update Finance');
    }

    public function destroy(User $user){
        Gate::authorize('finance.manage');
        abort_unless($user->role === 'finance', 404);
        $user->delete();
        return redirect()->back()->with('msg','Success Delete Finance');
    }
}
