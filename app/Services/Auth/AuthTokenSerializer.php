<?php

declare(strict_types=1);

namespace App\Services\Auth;

class AuthTokenSerializer
{
    public string $id;

    public string $ssNumber;

    public ?string $firstName;

    public ?string $fullName;

    public function __construct(string $id, string $ssNumber, ?string $firstName = null, ?string $fullName = null)
    {
        $this->id = $id;

        $this->ssNumber = $ssNumber;

        $this->firstName = $firstName;

        $this->fullName = $fullName;
    }

    public static function make(string $id, string $ssNumber, ?string $firstName = null, ?string $fullName = null)
    {
        return new static($id, $ssNumber, $firstName, $fullName);
    }

    public static function parse(string $authToken): static
    {
        $instance = new static('', '');

        if (! $authToken) {
            return $instance;
        }

        if (substr($authToken, 0, 2) !== 's:') {
            return $instance;
        }

        $authToken = substr($authToken, 2);

        $authToken = mute_exception(fn() => http_decrypt($authToken));

        if (! $authToken) {
            return $instance;
        }

        $authToken = json_decode($authToken, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $instance;
        }

        $instance->id = $authToken['id'] ?? '';
        $instance->ssNumber = $authToken['ss_number'] ?? '';
        $instance->firstName = $authToken['first_name'] ?? '';
        $instance->fullName = $authToken['full_name'] ?? '';

        return $instance;
    }

    public function compile(): string
    {
        if ($this->id && $this->ssNumber) {
            return 's:' . http_encrypt(json_encode([
                'id' => $this->id,
                'ss_number' => $this->ssNumber,
                'first_name' => $this->firstName,
                'full_name' => $this->fullName,
            ]));
        }

        return '';
    }

    public function exists(): bool
    {
        return ! (empty($this->id) || empty($this->ssNumber));
    }
}
