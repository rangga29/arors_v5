<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('view', User::class);

        return view('backend.users.view', [
            'users' => User::with('roles')->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get()
        ]);
    }

    public function store(UserStoreRequest $request)
    {
        $this->authorize('create', User::class);

        $validateData = $request->validated();
        $user = User::create([
            'name' => $validateData['name'],
            'username' => $validateData['username'],
            'password' => Hash::make($validateData['password']),
            'created_by' => $validateData['created_by']
        ]);
        $getRole = Role::where('id', $validateData['role'])->first()->name;
        $user->assignRole($getRole);
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'USER',
            'lo_message' => 'CREATE : ' . $validateData['username']
        ]);
        return redirect()->route('users')->with('success', 'Data User Berhasil Ditambahkan');
    }

    public function show(User $user)
    {
        $this->authorize('view', User::class);

        $data = User::where('username', $user->username)->first();
        return response()->json($data);
    }

    public function getRoleByUser(User $user)
    {
        $this->authorize('view', User::class);

        $data = $user->roles->first();
        return response()->json($data);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $this->authorize('edit', User::class);

        $validateData = $request->validated();
        if($validateData['password'] != null) {
            $user->update([
                'name' => $validateData['name'],
                'username' => $validateData['username'],
                'password' => Hash::make($validateData['password']),
                'updated_by' => $validateData['updated_by']
            ]);
        } else {
            $user->update([
                'name' => $validateData['name'],
                'username' => $validateData['username'],
                'updated_by' => $validateData['updated_by']
            ]);
        }
        $getRole = Role::where('id', $validateData['role'])->first()->name;
        $user->syncRoles([$getRole]);
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'USER',
            'lo_message' => 'UPDATE : ' . $validateData['username']
        ]);
        return redirect()->route('users')->with('success', 'Data User Berhasil Diubah');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', User::class);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'USER',
            'lo_message' => 'DELETE : ' . $user->username
        ]);
        $user->delete();
        return redirect()->route('users')->with('success', 'Data User Berhasil Dihapus');
    }

    public function login()
    {
        return view('backend.auth.login');
    }

    public function authentication(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'USER',
            'lo_message' => 'LOGIN : ' . auth()->user()->username
        ]);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}
