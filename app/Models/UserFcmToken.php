<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFcmToken extends Model
{
    protected $fillable = [
        'recipient_id',
        'recipient_type',
        'token',
        'device_type',
    ];
}
