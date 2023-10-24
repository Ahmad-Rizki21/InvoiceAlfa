<?php

namespace App\Services\Database\Eloquent;

use App\Services\Auth\Contracts\AuthenticatableUser as UserContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Services\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

abstract class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    UserContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasApiTokens;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function makeToken(array $payload)
    {
        $ipAddress = $payload['ip_address'] ?? null;
        $userAgent = $payload['user_agent'] ?? null;

        if ($ipAddress && $userAgent) {
            $this->tokens()->where('ip_address', $ipAddress)->where('user_agent', $userAgent)->delete();
        }

        return $this->tokens()->create(array_merge($payload, [
            'name' => 'user',
            'token' => hash('sha256', Str::random(40)),
        ]));
    }
}
