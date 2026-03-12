<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('backend.users.profile', [
            'user' => User::where('username', auth()->user()->username)->first(),
            'logs' => Log::where('lo_user', auth()->user()->username)->orderBy('lo_time', 'DESC')->get()
        ]);
    }

    public function update(ProfileUpdateRequest $request, User $user)
    {
        $validateData = $request->validated();
        if($validateData['password'] != null) {
            $user->update([
                'name' => $validateData['name'],
                'password' => Hash::make($validateData['password']),
                'updated_by' => $validateData['updated_by']
            ]);
        } else {
            $user->update([
                'name' => $validateData['name'],
                'updated_by' => $validateData['updated_by']
            ]);
        }

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'PROFILE',
            'lo_message' => 'UPDATE : ' . $user->username
        ]);

        return redirect()->route('users.profile', auth()->user()->username)->with('success', 'Data Profile Berhasil Diubah');
    }
}
