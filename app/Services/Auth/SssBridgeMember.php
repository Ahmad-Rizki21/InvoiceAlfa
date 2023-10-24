<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Auth\Contracts\AuthenticatableUser;
use App\Services\Auth\Enums\UserType;
use Illuminate\Auth\GenericUser;

class SssBridgeMember extends GenericUser implements AuthenticatableUser
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes)
    {
        if (! isset($attributes[$this->getAuthIdentifierName()])) {
            $attributes[$this->getAuthIdentifierName()] = $attributes['ss_number'];
        }

        if (! isset($attributes[$this->getRememberTokenName()])) {
            $attributes[$this->getRememberTokenName()] = $attributes['ss_number'];
        }

        $attributes['password'] = $attributes['ss_number'];

        parent::__construct($attributes);
    }

    public function getId(): string
    {
        return (string) $this->getAuthIdentifier() ?? '';
    }

    public function getSsNumber(): string
    {
        return $this->attributes['ss_number'] ?? '';
    }

    public static function userType(): UserType
    {
        return UserType::Member;
    }
}
