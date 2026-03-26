<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements Wallet, JWTSubject
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, HasWallet;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identification_code',
        'name',
        'email',
        'password',
        'username',
        'phone_number',
        'agent_code',
        'status',
        'is_verified',
        'last_logined'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'wallet'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean'
        ];
    }
    protected $appends = ['balance'];

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

    public function getBalanceAttribute(): string
    {
        return $this->wallet->balance;
    }

    public function agent()
    {
        return $this->belongsTo(
            Agent::class,
            'agent_code',
            'agent_code'
        );
    }

    protected static function booted()
    {
        static::creating(function ($user) {

            if ($user->identification_code) {
                return;
            }

            $user->identification_code = self::generateUserCode();
        });
    }

    public static function generateUserCode(): string
    {
        return DB::transaction(function () {

            $totalUsers = self::lockForUpdate()->count();

            $blockSize = 1000;
            $alphabetCount = 26;
            $blockCycleSize = $blockSize * $alphabetCount;

            $currentIndex = $totalUsers;

            $cycle = intdiv($currentIndex, $blockCycleSize);
            $positionInCycle = $currentIndex % $blockCycleSize;
            $alphabetIndex = intdiv($positionInCycle, $blockSize);
            $numberInBlock = ($positionInCycle % $blockSize) + 1;

            $prefix = chr(ord('A') + $alphabetIndex);
            $finalNumber = ($cycle * $blockSize) + $numberInBlock;

            return $prefix . str_pad($finalNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
