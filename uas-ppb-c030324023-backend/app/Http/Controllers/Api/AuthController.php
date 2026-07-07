<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR',
            ], 422);
        }

        $account = Account::create([
            'role' => 'candidate',
            'nisn' => $request->nisn,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token' => $this->issueToken($account),
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => ['required', 'digits:10'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR',
            ], 422);
        }

        $account = Account::where('nisn', $request->nisn)->first();

        if (! $account || ! Hash::check($request->password, $account->password)) {
            return response()->json([
                'message' => 'NISN atau kata sandi salah.',
                'errors' => [],
                'code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $this->issueToken($account),
        ]);
    }

    private function issueToken(Account $account): string
    {
        $now = time();

        return JWT::encode([
            'sub' => $account->id,
            'role' => $account->role,
            'iat' => $now,
            'exp' => $now + 60 * 60 * 24,
        ], config('jwt.secret'), 'HS256');
    }
}
