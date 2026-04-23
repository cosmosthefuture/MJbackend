<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongRoundDiscard extends Model
{
    protected $table = 'mah_jong_round_discards';

    protected $fillable = [
        'mah_jong_match_id',
        'mah_jong_game_round_id',
        'tile_id',
        'user_id',
        'turn_no',
    ];

    public function match()
    {
        return $this->belongsTo(MahJongMatch::class, 'mah_jong_match_id');
    }

    public function round()
    {
        return $this->belongsTo(MahJongGameRound::class, 'mah_jong_game_round_id');
    }

    public function tile()
    {
        return $this->belongsTo(MahJongTile::class, 'tile_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
