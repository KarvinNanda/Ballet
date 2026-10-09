<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\ChangeProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function changeProfilePage(){
        $user = Auth::user();
        return view('profile.profile',compact('user'));
    }

    public function changeProfile(ChangeProfileRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

        $user->forceFill($data)->save();

        return redirect()->route('change-profile-page')->with('msg', 'Success Change Profile');
    }

    public function changePasswordPage(){
        $user = Auth::user();
        return view('profile.password',compact('user'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();

        // AuthenticateSession re-stores the new hash after this request, so this session stays logged in.
        $request->user()->forceFill(['password' => Hash::make($data['new_password'])])->save();

        return redirect()->route('change-password-page')->with('msg', 'Success Change Password');
    }
}
