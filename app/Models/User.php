<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const TIER_BASIC = 'basic';
    public const TIER_PRO = 'pro';
    public const TIER_PRO_PLUS = 'pro_plus';

    private const TIER_RANK = [
        self::TIER_BASIC => 1,
        self::TIER_PRO => 2,
        self::TIER_PRO_PLUS => 3,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    public function hasTierAtLeast(string $tier): bool
    {
        return (self::TIER_RANK[$this->tier] ?? 0) >= (self::TIER_RANK[$tier] ?? 99);
    }
}
