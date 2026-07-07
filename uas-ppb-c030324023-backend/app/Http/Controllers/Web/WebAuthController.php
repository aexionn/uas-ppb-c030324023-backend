<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // ponytail: one login field for both roles — candidates type their NISN,
        // admins type their username. Whichever column matches, matches.
        $account = Account::where('nisn', $request->identifier)
            ->orWhere('username', $request->identifier)
            ->first();

        if (! $account || ! Auth::attempt(['id' => $account->id, 'password' => $request->password])) {
            return back()->withErrors(['identifier' => 'NISN/Username atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => ['required', 'digits:10', 'unique:accounts,nisn'],
            'username' => ['required', 'string', 'unique:accounts,username'],
            'email' => ['required', 'email', 'unique:accounts,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'nisn.digits' => 'NISN harus terdiri dari 10 digit angka.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $account = Account::create([
            'role' => 'candidate',
            'nisn' => $request->nisn,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        Auth::login($account);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
