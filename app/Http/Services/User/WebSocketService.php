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

    public function generateTokenToConnectWs($user)
    {
        $token = JWTAuth::customClaims([
            'purpose' => 'websocket',
            'user_id' => $user->id,
            'name' => $user->name,
            'iat' => now()->timestamp,
        ])->fromUser($user);

        return [
            'ws_token' => $token,
            'expires_in_sec' => 5 * 60,
        ];
    }

        public function generateTokenToJoinRoom($user, $roomId)
    {
        $token = JWTAuth::customClaims([
            'purpose' => 'mahjong_join',
            'user_id' => $user->id,
            'room_id' => $roomId,
            'iat' => now()->timestamp,
        ])->fromUser($user);

        return [
            'ws_token' => $token,
            'expires_in_sec' => 5 * 60,
        ];
    }
}
