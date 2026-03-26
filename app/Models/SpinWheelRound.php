<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SpinWheelRound extends Model
{
    protected $fillable = [
        'game_room_id',
        'round_number',
        'status',
        'started_at',
        'betting_closed_at',
        'finished_at',
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
        if (array_key_exists('betting_closed_at', $attributes)) {
            $attributes['betting_closed_at'] = Carbon::parse($attributes['betting_closed_at'])->format('Y-m-d H:i:s');
        }
        if (array_key_exists('finished_at', $attributes)) {
            $attributes['finished_at'] = Carbon::parse($attributes['finished_at'])->format('Y-m-d H:i:s');
        }
        return $attributes;
    }

    public function gameRoom()
    {
        return $this->belongsTo(GameRoom::class);
    }

    public function bets()
    {
        return $this->hasMany(SpinWheelBet::class, 'spin_wheel_round_id');
    }

    public function result()
    {
        return $this->hasOne(SpinWheelResult::class);
    }
}
