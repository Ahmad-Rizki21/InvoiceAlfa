<?php

namespace App\Services\Encrypter;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class TrxCrypter
{
    public static function encrypt(string $string, int $expiry = 25): string
    {
        $chunk = collect(str_split(((string) mt_rand(1000, 9999)) . $string))->chunk(4);
        $newChunk = collect();
        $expiryDate = Carbon::now()->addHour($expiry)->timestamp;
        $randomString = preg_replace('~[^a-zA-Z]~', '', Str::random());
        $expiryDateChunk = collect(str_split(str_pad((string) $expiryDate, 16, strtoupper($randomString))))->chunk(4);
        $dot = 2;

        foreach ($chunk as $i => $value) {
            if ($i === $dot && count($expiryDateChunk)) {
                $newChunk[] = $expiryDateChunk->shift()->reverse();
                $dot++;
            }

            $newChunk[] = $value->reverse();
        }

        $result = 'T' . $newChunk->flatten()->implode('');

        if (strlen($result) >= 50) {
            return $string . 'N0EN';
        }

        return $result;
    }

    public static function decrypt(string $decrypted, bool $checkExpiry = false, bool $getExpiryDate = false): string
    {
        if ($decrypted && substr($decrypted, -4) === 'N0EN') {
            return substr($decrypted, 0, strlen($decrypted) - 4);
        }

        $decrypted = substr($decrypted, 1);

        $chunk = collect(str_split($decrypted))->chunk(4);
        $result = collect();
        $expiryDateChunk = collect();

        foreach ($chunk as $i => $value) {
            if ($i === 0) {
                continue;
            }

            if (in_array($i, [2, 4, 6, 8])) {
                $expiryDateChunk[] = $value->reverse();
            } else {
                $result[] = $value->reverse();
            }
        }

        if ($getExpiryDate) {
            return $expiryDateChunk->flatten()->implode('');
        }

        return $result->flatten()->implode('');
    }

    public static function isEncryptedReferenceNoExpired(string $decrypted): bool
    {
        $expiryDate = static::decrypt($decrypted, false, true);

        if ($expiryDate) {
            try {
                return Carbon::parse((int) preg_replace('~[^0-9\\.]~', '', $expiryDate))->lt(Carbon::now());
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }
}
