<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CoinFlipPayout extends Model
{
    protected $fillable = [
        'coin_flip_round_id',
        'user_id',
        'total_bet_amount',
        'win_amount',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
