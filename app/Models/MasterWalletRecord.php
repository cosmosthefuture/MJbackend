<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterWalletRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'master_id',
        'date_time',
        'type',
        'amount',
        'balance',
        'description'
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
        if (array_key_exists('date_time', $attributes)) {
            if ($attributes['date_time'] !== null) {
                $attributes['date_time'] = Carbon::parse($attributes['date_time'])->format('Y-m-d H:i:s');
            }
        }
        return $attributes;
    }
}
