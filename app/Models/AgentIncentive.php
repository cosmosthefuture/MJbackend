<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgentIncentive extends Model
{
    protected $fillable = [
        'date_time',
        'month',
        'agent_id',
        'user_id',
        'agent_code',
        'deposit_amount',
        'incentive_percentage',
        'incentive_amount',
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
        if (array_key_exists('date_time', $attributes)) {
            if ($attributes['date_time'] !== null) {
                $attributes['date_time'] = Carbon::parse($attributes['date_time'])->format('Y-m-d H:i:s');
            }
        }
        return $attributes;
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
