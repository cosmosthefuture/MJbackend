<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongRoundAction extends Model
{
    protected $table = 'mah_jong_round_actions';

    protected $fillable = [
        'mah_jong_match_id',
        'mah_jong_game_round_id',
        'user_id',
        'action_type',
        'payload',
        'turn_no',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function match()
    {
        return $this->belongsTo(MahJongMatch::class, 'mah_jong_match_id');
    }

    public function round()
    {
        return $this->belongsTo(MahJongGameRound::class, 'mah_jong_game_round_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
