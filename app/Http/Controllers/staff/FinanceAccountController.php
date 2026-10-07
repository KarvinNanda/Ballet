<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Mail\SendingEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FinanceAccountController extends Controller
{
    public function index(Request $request){
        $finances = User::where('role','finance')
            ->when($request->query('search'), fn ($q, $s) => $q->where('name','like',"%{$s}%"))
            ->orderBy('id','desc')
            ->paginate(5)
            ->withQueryString();
        return view('staff.finance.index',compact('finances'));
    }

    public function create(){
        return view('staff.finance.insert');
    }

    public function store(Request $req){
        $rules = [
            'inputName' => 'required|string|max:255',
            'inputEmail' => 'required|email:filter|unique:users,email',
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
        $user->role = 'finance';
        $user->dob = $req->inputDate_of_Birth;
        $user->email = $req->inputEmail;
        $user->phone = $req->inputPhone;
        $user->password = bcrypt('ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY'));
        $user->save();

        $credential = [
            'email' => $req->inputEmail,
            'password' => 'ballet'.Carbon::parse($req->inputDate_of_Birth)->format('dmY')
        ];

        Mail::to($user->email)->send(new SendingEmail($credential));

        return redirect(staff_route('finance.index'))->with('msg','Success Create Finance Data');
    }

    public function edit(User $user){
        Gate::authorize('finance.manage');
        abort_unless($user->role === 'finance', 404);
        $return_url = url()->previous();
        return view('staff.finance.update',compact('user','return_url'));
    }

    public function update(Request $req,User $user){
        Gate::authorize('finance.manage');
        abort_unless($user->role === 'finance', 404);
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
