<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MahJongGameRuleFee extends Model
{
    protected $fillable = [
        'mah_jong_game_rule_id',
        'fee_type',
        'amount',
        'payer_type',
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
        if (array_key_exists('deleted_at', $attributes)) {
            if ($attributes['deleted_at'] !== null) {
                $attributes['deleted_at'] = Carbon::parse($attributes['deleted_at'])->format('Y-m-d H:i:s');
            }
        }
        return $attributes;
    }

    public function rule()
    {
        return $this->belongsTo(MahJongGameRule::class, 'mah_jong_game_rule_id');
    }

    public function isPaidByWinner(): bool
    {
        return $this->payer_type === 'winner';
    }

    public function isPaidByEachPlayer(): bool
    {
        return $this->payer_type === 'each_player';
    }
}
