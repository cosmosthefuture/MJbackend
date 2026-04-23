<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongMatch extends Model
{
    protected $table = 'mah_jong_matches';

    protected $fillable = [
        'mah_jong_game_room_id',
        'first_player_id',
        'status',
        'total_rounds',
        'current_round_id',
        'current_turn_user_id',
    ];

    public function room()
    {
        return $this->belongsTo(MahJongGameRoom::class, 'mah_jong_game_room_id');
    }

    public function rounds()
    {
        return $this->hasMany(MahJongGameRound::class, 'mah_jong_match_id');
    }

    public function players()
    {
        return $this->hasMany(MahJongMatchPlayer::class, 'mah_jong_match_id');
    }

    public function firstPlayer()
    {
        return $this->belongsTo(User::class, 'first_player_id');
    }
}
