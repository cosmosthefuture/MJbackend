<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongRoomPlayer extends Model
{
    protected $table = 'mah_jong_room_players';

    protected $fillable = [
        'mah_jong_game_room_id',
        'user_id',
        'seat_position',
        'is_ready',
        'has_paid_fee',
        'is_active',
        'last_round_played_at'
    ];

    public function room()
    {
        return $this->belongsTo(MahJongGameRoom::class, 'mah_jong_game_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
