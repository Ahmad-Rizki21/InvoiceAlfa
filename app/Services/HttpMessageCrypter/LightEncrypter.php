<?php

namespace App\Services\HttpMessageCrypter;

use Exception;

class LightEncrypter
{
    protected $salt = '';

    protected $resultPrefix = 'eyJpdiI6IkV';
    protected $resultSuffix = ':(hmc)==';

    protected $replacements = [
        '"' => '=!@',
        ':' => '=@#',
        ',' => '=#$',
        // '{' => '=$%',
        // '}' => '=%^',
        'true' => '=^&',
        'false' => '=&*',
        'success' => '=*(',
        'status' => '=()',
        // "\n" => '=)_',
        // ' ' => '=-+',
    ];

    protected $alphabets = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    public function getSalt(): string
    {
        return (config('http-message-crypter.salt') ?: '') . $this->salt;
    }

    public function encrypt($str): string
    {
        $salt = $this->toChars($this->getSalt());

        $splittedValue = str_split($str, 16);
        $splittedValueLen = count($splittedValue);
        $str = '';

        $replacementKeys = array_keys($this->replacements);
        $replacementValues = array_values($this->replacements);

        foreach ($splittedValue as $i => $val) {
            $val = str_replace($replacementKeys, $replacementValues, $val);
            $val = urlencode(($salt[$i] ?? '') . $val);
            $str .= $i >= $splittedValueLen - 1 ? $val : str_pad($val, 96, $this->stringRandom(16), STR_PAD_RIGHT);
            // $str .= str_pad($val, 96, $i >= $splittedValueLen - 1 ? '*' : $this->stringRandom(16), STR_PAD_RIGHT);
        }

        return $this->resultSuffix.base64_encode($str);
    }

    public function decrypt($hash): string
    {
        try {
            $str = base64_decode(str_replace($this->resultSuffix, '', $hash));
            $splittedStr = str_split($str, 96);
            $splittedStrLen = count($splittedStr);
            $reversedSalt = ($this->toChars($this->getSalt()));
            $result = [];

            $replacementKeys = array_keys($this->replacements);
            $replacementValues = array_values($this->replacements);

            foreach ($splittedStr as $i => $val) {
                $val = urldecode($val);

                $val = str_replace($replacementValues, $replacementKeys, $val);
                $saltLength = 0;

                if (isset($reversedSalt[$i])) {
                    $salt = $reversedSalt[$i] . '';
                    $saltLength = strlen('' . $salt);

                    if (substr($val, 0, $saltLength) === $salt) {
                        $val = substr($val, $saltLength);
                    }
                }

                if ($i >= $splittedStrLen - 1) {
                    $result[] = preg_replace('~\*+~', '', $val);
                } else {
                    $result[] = str_split($val, 16)[0];
                }
            }

            return implode('', $result);
        } catch (\Throwable $e) {
            throw new Exception('Unable to load requested data. Please try again.');
        }
    }

    protected function toChars($str): array
    {
        $len = mb_strlen($str);
        $result = [];
        $i = 0;

        while ($i < $len) {
            $result[] = $this->charCodeAt($str, $i++);
        }

        return $result;
    }

    protected function charCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');

        if (mb_check_encoding($char, 'UTF-8')) {
            return hexdec(bin2hex(mb_convert_encoding($char, 'UTF-32BE', 'UTF-8')));
        }

        return null;
    }

    public function stringRandom($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function encryptPoly(string $str): string
    {
        $splittedValue = mb_str_split($str, 16);
        $result = '';

        $replacementKeys = array_keys($this->replacements);
        $replacementValues = array_values($this->replacements);

        foreach ($replacementKeys as $key => $value) {
            $replacementKeys[$key] = '~' . $value . '~';
        }

        foreach ($splittedValue as $i => $value) {
                // if ($i % 2 === 0) {
                    $currentResult= $this->encryptPolyChars($value);
                    $result .= $currentResult;
                // } else {
                    // $value = preg_replace($replacementKeys, $replacementValues, $value);
                    // $result .= $value;
                // }
        }

        // $result = preg_replace($replacementKeys, $replacementValues, $result);

        $res = '';
        // dump(['before_base64' => $result]);
        $splittedResult = mb_str_split($result, 1000000);
        foreach ($splittedResult as $value) {
            $res .= base64_encode($value);
        }


        return $this->resultPrefix . $res . $this->resultSuffix;
    }

    protected function encryptPolyChars(string $str): string
    {
        $splittedLetter = mb_str_split($str);
        $salt = $this->getSalt();
        $splittedSalt = mb_str_split($salt);
        $saltLen = count($splittedSalt);
        $latestSaltIndex = 0;

        $result = '';

        foreach ($splittedLetter as $i => $letter) {
            if ($latestSaltIndex >= $saltLen) {
                $latestSaltIndex = 0;
            }

            $result .= $this->encryptPolyLetter($splittedSalt[$latestSaltIndex] ?: '', $letter);

            $latestSaltIndex++;
        }

        return $result;
    }

    protected function encryptPolyLetter(string $saltLetter, string $letter): string
    {
        $alphabets = $this->alphabets;
        $letterPos = strpos($alphabets, $letter);

        if ($letterPos === false) {
            return $letter;
        }

        $saltLetterPos = strpos($alphabets, $saltLetter);

        if ($saltLetterPos === false) {
            return $letter;
        }

        $part1 = substr($alphabets, $letterPos, strlen($alphabets));
        $part2 = substr($alphabets, 0, $letterPos);
        $newAlphabets = $part1 . $part2;

        return substr($newAlphabets, $saltLetterPos, 1);
    }

    public function decryptPoly(string $str): string
    {
        $str = preg_replace('~^'.$this->resultPrefix.'~', '', $str);
        $str = str_replace($this->resultSuffix, '', $str);

        $res = '';
        $splittedValue = mb_str_split($str, 1000000);
        foreach ($splittedValue as $value) {
            $res .= base64_decode($value);
            // $res .= $value;
        }
        $str = $res;
        $replacementKeys = array_keys($this->replacements);
        $replacementValues = array_values($this->replacements);


        foreach ($replacementValues as $key => $value) {
            $splitted = str_split($value);
            $value = '';

            foreach ($splitted as $v) {
                $value .= '\\' . $v;
            }
            $replacementValues[$key] = '~' . $value . '~';
        }
        $str = preg_replace($replacementValues, $replacementKeys, $str);

        // $str = base64_decode($str);
        // $str = urldecode($str);
        // $str = str_replace(array_values($this->replacements), array_keys($this->replacements), $str);

        $splittedValue = mb_str_split($str, 16);
        $result = '';

        foreach ($splittedValue as $i => $value) {
            // if ($i % 2 === 0) {
                $currentValue = $this->decryptPolyChars($value);
                $result .= $currentValue;
            // } else {
                // $value = preg_replace($replacementValues, $replacementKeys, $value);
                // $result .= $value;
            // }
        }

        // dump(['result'=> $result]);
        return $result;
    }

    protected function decryptPolyChars(string $str): string
    {
        $splittedLetter = mb_str_split($str);
        $salt = $this->getSalt();
        $splittedSalt = mb_str_split($salt);
        $saltLen = count($splittedSalt);
        $latestSaltIndex = 0;

        $result = '';

        foreach ($splittedLetter as $i => $letter) {
            if ($latestSaltIndex >= $saltLen) {
                $latestSaltIndex = 0;
            }

            $result .= $this->decryptPolyLetter($splittedSalt[$latestSaltIndex] ?: '', $letter);

            $latestSaltIndex++;
        }

        return $result;
    }

    protected function decryptPolyLetter(string $saltLetter, string $letter): string
    {
        $alphabets = $this->alphabets;
        $saltLetterPos = strpos($alphabets, $saltLetter);

        if ($saltLetterPos === false) {
            return $letter;
        }

        $part1 = substr($alphabets, $saltLetterPos, strlen($alphabets));
        $part2 = substr($alphabets, 0, $saltLetterPos);
        $newAlphabets = $part1 . $part2;

        $letterPos = strpos($newAlphabets, $letter);

        if ($letterPos === false) {
            return $letter;
        }

        return substr($alphabets, $letterPos, 1);
    }
}

