<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongMatchPlayer extends Model
{
    protected $table = 'mah_jong_match_players';

    protected $fillable = [
        'mah_jong_match_id',
        'user_id',
        'seat_position',
        'is_active',
    ];

    public function match()
    {
        return $this->belongsTo(MahJongMatch::class, 'mah_jong_match_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
