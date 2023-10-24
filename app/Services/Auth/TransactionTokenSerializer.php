<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Stringable;

class TransactionTokenSerializer implements Stringable
{
    public ?string $token = null;

    public CarbonInterface|DateTimeInterface|null $expiredAt = null;

    public function __construct(string $token = null, DateTimeInterface|int|null $expiredAt = null)
    {
        $this->token = $token;

        $this->expiredAt = $expiredAt ? Carbon::parse($expiredAt) : null;
    }

    public static function make(string $token, DateTimeInterface|int $expiredAt)
    {
        return new static($token, $expiredAt);
    }

    public static function parse(string $tranToken): static
    {
        $instance = new static();

        if (! $tranToken) {
            return $instance;
        }

        $instance->token = $tranToken;
        $instance->expiredAt = Carbon::now();

        return $instance;
    }

    public static function parseFromSss(string $tranToken, DateTimeInterface|int|null $expiredAt = null): static
    {
        return new static(
            substr($tranToken, 12),
            $expiredAt ? $expiredAt : Carbon::now()->addMinute(4)->addSeconds(50)
        );
    }

    public function compile(): string
    {
        if ($this->token && $this->expiredAt) {
            return $this->token;
        }

        return '';
    }

    public function exists(): bool
    {
        return ! empty($this->token);
    }

    public function isExpired(): bool
    {
        return false;
    }

    public function __toString()
    {
        return $this->token;
    }
}
