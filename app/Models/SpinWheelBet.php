<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SpinWheelBet extends Model
{
    protected $fillable = [
        'spin_wheel_round_id',
        'user_id',
        'bet_amount',
        'total_winning_chance_percentage',
        'single_bet_winning_percentage',
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
        return $this->belongsTo(SpinWheelRound::class, 'spin_wheel_round_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
