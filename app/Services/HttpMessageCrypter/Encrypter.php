<?php

declare(strict_types=1);

namespace App\Services\HttpMessageCrypter;

use Exception;

class Encrypter
{
    protected $key =  'secretessecretes';

    protected $cipher = 'aes-128-cbc';

    protected $resultSuffix = ':(rst)==';

    public function __construct()
    {
        $this->key = config('http-message-crypter.salt');
    }

    public function setKey(string $key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function encrypt(string $value): string
    {
        $iv = random_bytes(openssl_cipher_iv_length(strtolower($this->cipher)));

        $value = \openssl_encrypt($value,
            strtolower($this->cipher), $this->getKey(), 0, $iv, $tag
        );

        if ($value === false) {
            throw new Exception('Could not encrypt the data.');
        }

        $iv = base64_encode($iv);
        $tag = base64_encode($tag ?? '');

        $mac = $this->hash($iv, $value);

        $json = json_encode(compact('iv', 'value', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Could not encrypt the data.');
        }

        $result = [];

        $splittedResult = mb_str_split($json, 100000);
        foreach ($splittedResult as $value) {
            $result[] = base64_encode($value);
        }

        return implode('.', $result) . $this->resultSuffix;
    }

    public function decrypt(string $payload): string
    {
        $payload = str_replace($this->resultSuffix, '', $payload);
        $splittedPayload = explode('.', $payload);

        $val = '';
        foreach ($splittedPayload as $value) {
            $val .= base64_decode($value);
        }
        $payload = $val;

        $payload = json_decode($payload, true);

        $invalidPayloadException = new Exception('The payload is invalid.');

        if (! is_array($payload)) {
            throw $invalidPayloadException;
        }

        foreach (['iv', 'value', 'mac'] as $item) {
            if (! isset($payload[$item]) || ! is_string($payload[$item])) {
                throw $invalidPayloadException;
            }
        }

        if (isset($payload['tag']) && ! is_string($payload['tag'])) {
            throw $invalidPayloadException;
        }

        if (strlen(base64_decode($payload['iv'], true)) !== openssl_cipher_iv_length(strtolower($this->cipher))) {
            throw $invalidPayloadException;
        }

        $validMac = hash_equals(
            $this->hash($payload['iv'], $payload['value']),
            $payload['mac']
        );

        if (! $validMac) {
            throw new Exception('The MAC is invalid');
        }

        $iv = base64_decode($payload['iv']);

        $tag = empty($payload['tag']) ? null : base64_decode($payload['tag']);

        if (is_string($tag)) {
            throw new Exception('Unable to use tag because the cipher algorithm does not support AEAD.');
        }

        $decrypted = \openssl_decrypt(
            $payload['value'], strtolower($this->cipher), $this->getKey(), 0, $iv, $tag ?? ''
        );

        if ($decrypted === false) {
            throw new Exception('Could not decrypt the data.');
        }

        return $decrypted;
    }

    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, $this->getKey());
    }

    public function isEncrypted(string $string)
    {
        return strpos($string, $this->resultSuffix) !== false;
    }
}
