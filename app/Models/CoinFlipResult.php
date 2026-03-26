<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CoinFlipResult extends Model
{
    protected $fillable = [
        'coin_flip_round_id',
        'result_side',
        // 'main_player_user_id',
        'total_head_bet',
        'total_tail_bet',
        'total_pot',
        'house_cut',
    ];

    public function toArray()
    {
        $attributes = parent::toArray();
        if (array_key_exists('created_at', $attributes)) {
            $attributes['created_at'] = Carbon::parse($attributes['created_at'])->format('Y-m-d H:i:s');
        }
        if (array_key_exists('updated_at', $attributes)) {
            $attributes['updated_at'] = Carbon::parse($attributes['updated_at'])->format('Y-m-d H:i:s');
        }
        return $attributes;
    }

    public function round()
    {
        return $this->belongsTo(CoinFlipRound::class, 'coin_flip_round_id');
    }

    // public function mainPlayer()
    // {
    //     return $this->belongsTo(User::class, 'main_player_user_id');
    // }
}
