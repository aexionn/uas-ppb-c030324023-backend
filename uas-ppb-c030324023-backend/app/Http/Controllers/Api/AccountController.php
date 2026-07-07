<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function me(Request $request)
    {
        $account = $request->user();

        return response()->json([
            'id' => $account->id,
            'role' => $account->role,
            'nisn' => $account->nisn,
            'username' => $account->username,
            'email' => $account->email,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR',
            ], 422);
        }

        $account = $request->user();

        if (! Hash::check($request->current_password, $account->password)) {
            return response()->json([
                'message' => 'Kata sandi saat ini salah.',
                'errors' => [],
                'code' => 'INVALID_CURRENT_PASSWORD',
            ], 422);
        }

        $account->update(['password' => $request->new_password]);

        return response()->json(['message' => 'Kata sandi berhasil diubah.']);
    }
}
