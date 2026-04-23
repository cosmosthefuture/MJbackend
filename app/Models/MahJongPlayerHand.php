<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongPlayerHand extends Model
{
    protected $table = 'mah_jong_player_hands';

    protected $fillable = [
        'mah_jong_match_id',
        'user_id',
        'tiles',
    ];

    protected $casts = [
        'tiles' => 'array',
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
