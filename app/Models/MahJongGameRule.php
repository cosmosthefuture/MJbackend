<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MahJongGameRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'game_id',
        'rule_name',
        'match_qty_per_round',
        'max_player',
        'bet_amount',
        'created_by',
        'updated_by',
        'status'
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

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function fees()
    {
        return $this->hasMany(MahJongGameRuleFee::class, 'mah_jong_game_rule_id');
    }

    public function getFeeByType(string $type)
    {
        return $this->fees->firstWhere('fee_type', $type);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
