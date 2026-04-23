<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongRoundTile extends Model
{
    protected $table = 'mah_jong_game_round_tiles';

    protected $fillable = [
        'mah_jong_round_id',
        'mah_jong_tile_id',
        'location',
        'user_id',
        'sequence_order',
    ];

    public function round()
    {
        return $this->belongsTo(MahJongGameRound::class, 'mah_jong_round_id');
    }

    public function tile()
    {
        return $this->belongsTo(MahJongTile::class, 'mah_jong_tile_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
