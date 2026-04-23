<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongGameRound extends Model
{
    protected $table = 'mah_jong_game_rounds';

    protected $fillable = [
        'mah_jong_match_id',
        'round_no',
        'winner_user_id',
        'status',
    ];

    public function match()
    {
        return $this->belongsTo(MahJongMatch::class, 'mah_jong_match_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function actions()
    {
        return $this->hasMany(MahJongRoundAction::class, 'mah_jong_game_round_id');
    }

    public function tiles()
    {
        return $this->hasMany(MahJongRoundTile::class, 'mah_jong_round_id');
    }
}
