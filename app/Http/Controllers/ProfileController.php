<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ProfileController extends Controller
{
    public function changeProfilePage(){
        $user = Auth::user();
        return view('profile.profile',compact('user'));
    }

    public function changeProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email:filter', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric', 'digits_between:10,12'],
        ]);

        $user->forceFill($data)->save();

        return redirect()->route('change-profile-page')->with('msg', 'Success Change Profile');
    }

    public function changePasswordPage(){
        $user = Auth::user();
        return view('profile.password',compact('user'));
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'new_password' => ['required', PasswordRule::min(8)],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        // AuthenticateSession re-stores the new hash after this request, so this session stays logged in.
        $request->user()->forceFill(['password' => Hash::make($data['new_password'])])->save();

        return redirect()->route('change-password-page')->with('msg', 'Success Change Password');
    }
}
