<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Token tidak ditemukan.',
                'errors' => [],
                'code' => 'TOKEN_MISSING',
            ], 401);
        }

        try {
            $payload = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        } catch (ExpiredException) {
            return response()->json([
                'message' => 'Token sudah kedaluwarsa.',
                'errors' => [],
                'code' => 'TOKEN_EXPIRED',
            ], 401);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Token tidak valid.',
                'errors' => [],
                'code' => 'TOKEN_INVALID',
            ], 401);
        }

        $account = Account::find($payload->sub);

        if (! $account) {
            return response()->json([
                'message' => 'Token tidak valid.',
                'errors' => [],
                'code' => 'TOKEN_INVALID',
            ], 401);
        }

        $request->setUserResolver(fn () => $account);

        return $next($request);
    }
}
