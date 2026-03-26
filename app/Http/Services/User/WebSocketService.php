<?php

namespace App\Http\Services\User;

use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebSocketService
{
    public function __construct()
    {
        //
    }

    public function generateToken($user)
    {
        $token = JWTAuth::customClaims([
            'purpose' => 'websocket',
            'user_id' => $user->id,
            'iat' => now()->timestamp,
        ])->fromUser($user);

        return [
            'ws_token' => $token,
            'expires_in_sec' => 5 * 60,
        ];
    }
}
