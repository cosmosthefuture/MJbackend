<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MahJongGameRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mah_jong_game_rule_id',
        'game_id',
        'room_name',
        'room_code',
        'status',
        'created_by',
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

    public function gameRule()
    {
        return $this->belongsTo(MahJongGameRule::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
