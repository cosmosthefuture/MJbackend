<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserGameHistory extends Model
{
    protected $fillable = [
        'user_id',
        'game_type',
        'bet_amount',
        'win_amount',
        'status',
        'room_name',
        'round_number',
        'game_round_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
