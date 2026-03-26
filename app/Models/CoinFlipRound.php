<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CoinFlipRound extends Model
{
    protected $fillable = [
        'game_room_id',
        'round_number',
        'status',
        'started_at',
        'betting_closed_at',
        'finished_at',
        'total_head_bet',
        'total_tail_bet',
        'head_winning_chance_percentage',
        'tail_winning_chance_percentage',
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

    public function gameRoom()
    {
        return $this->belongsTo(GameRoom::class);
    }

    public function bets()
    {
        return $this->hasMany(CoinFlipBet::class);
    }

    public function result()
    {
        return $this->hasOne(CoinFlipResult::class);
    }

    public function payouts()
    {
        return $this->hasMany(CoinFlipPayout::class);
    }
}
