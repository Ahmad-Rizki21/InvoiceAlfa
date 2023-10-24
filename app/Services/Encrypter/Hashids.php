<?php

namespace App\Services\Encrypter;

use Sqids\Sqids as BaseHashids;

class Hashids
{
    public static function encodeUserId($userId)
    {
        if (! is_numeric($userId)) {
            return '0ix' . $userId;
        }

        return static::userIdHasher()->encode(['198823', $userId]);
    }

    public static function decodeUserId($encodedUserId)
    {
        if (strpos($encodedUserId, '0ix') === 0) {
            return substr($encodedUserId, 2);
        }

        $decoded = static::userIdHasher()->decode($encodedUserId);

        if (is_array($decoded) && count($decoded) === 2 && $decoded[0] == '198823' && is_numeric($decoded[1])) {
            return $decoded[1];
        }

        return null;
    }

    public static function encodeInvoiceId($invoiceId)
    {
        if (! is_numeric($invoiceId)) {
            return '1iv' . $invoiceId;
        }

        return static::invoiceIdHasher()->encode(['88273', $invoiceId]);
    }

    public static function decodeInvoiceId($encodedUserId)
    {
        if (strpos($encodedUserId, '0ix') === 0) {
            return substr($encodedUserId, 2);
        }

        $decoded = static::invoiceIdHasher()->decode($encodedUserId);

        if (is_array($decoded) && count($decoded) === 2 && $decoded[0] == '88273' && is_numeric($decoded[1])) {
            return $decoded[1];
        }

        return null;
    }

    protected static function userIdHasher()
    {
        return new BaseHashids('aPQRSTUVWXYZ012bcdevwxyzABCDEFGHIJKLfghijklmnopqrstuMNO3456789', 6);
    }

    protected static function invoiceIdHasher()
    {
        return new BaseHashids('aPQRSTUVWABCDEFGHIJKLfghijklmnopqrstuMNXYZ012bcdevwxyzO3456789', 28);
    }
}
