<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        // 'email',
        'phone_number',
        'username',
        'password',
        'status',
        'force_reset_password',
        'last_logined',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'force_reset_password' => 'boolean',
        ];
    }

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
        if (array_key_exists('last_logined', $attributes)) {
            if ($attributes['last_logined'] !== null) {
                $attributes['last_logined'] = Carbon::parse($attributes['last_logined'])->format('Y-m-d H:i:s');
            }
        }
        return $attributes;
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function permissionType()
    {
        return $this->belongsTo(PermissionType::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->whereHas('permissionType', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }
}
