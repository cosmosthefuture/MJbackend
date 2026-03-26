<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GlobalCommissionSetting extends Model
{
    protected $fillable = [
        'name',
        'key',
        'value',
        'type',
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

    protected static function booted()
    {
        static::creating(function ($model) {

            if (empty($model->key)) {
                $model->key = Str::snake($model->name);
            }
        });
    }
}
