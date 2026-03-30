<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDepositRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'action_by',
        'date_time'
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(Agent::class, 'action_by');
    }
}
