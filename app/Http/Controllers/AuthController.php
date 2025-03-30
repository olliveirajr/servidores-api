<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Gera o token de acesso (TTL padrão = 5 minutos)
        if (!$accessToken = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        // Gera o refresh token (TTL maior, ex: 7 dias)
        $refreshToken = JWTAuth::customClaims([
            'type' => 'refresh_token',
            'exp' => now()->addDays(7)->timestamp
        ])->fromUser(auth()->user());

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            // Obtém o token do header "Authorization"
            $refreshToken = JWTAuth::getToken();

            // Verifica se o token é um refresh token
            $payload = JWTAuth::getPayload($refreshToken);
            if ($payload->get('type') !== 'refresh_token') {
                return response()->json(['error' => 'Token não é um refresh token'], 401);
            }

            // Gera novo access token
            $newAccessToken = auth()->refresh();

            return response()->json([
                'access_token' => $newAccessToken,
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Refresh token expirado'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Refresh token inválido'], 401);
        }
    }

    public function user()
    {
        return auth()->user();
    }
}
