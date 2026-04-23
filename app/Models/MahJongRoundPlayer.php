<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongRoundPlayer extends Model
{
    protected $table = 'mah_jong_round_players';

    protected $fillable = [
        'mah_jong_game_round_id',
        'user_id',
        'seat_position',
        'is_first_player',
        'is_winner',
        'is_active',
        'is_auto',
        'last_action_at',
    ];

    protected $casts = [
        'is_first_player' => 'boolean',
        'is_winner' => 'boolean',
        'is_active' => 'boolean',
        'is_auto' => 'boolean',
        'last_action_at' => 'datetime',
    ];

    public function round()
    {
        return $this->belongsTo(MahJongGameRound::class, 'mah_jong_game_round_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    public function scopeFirstPlayer($query)
    {
        return $query->where('is_first_player', true);
    }
}
