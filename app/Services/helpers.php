<?php

use App\Services\Encrypter\Hashids;
use App\Services\Encrypter\TrxCrypter;
use App\Services\HttpMessageCrypter\Encrypter as HttpEncrypter;
use App\Services\SecureHeaders\SecureHeaders;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

if (! function_exists('errorValidation')) {
    function errorValidation($messages): ValidationException
    {
        $validator = app('validator')->make([], []);

        foreach ($messages as $key => $value) {
            foreach (Arr::wrap($value) as $message) {
                $validator->errors()->add($key, $message);
            }
        }

        return new ValidationException($validator);
    }
}

if (! function_exists('num2alpha')) {
    function num2alpha($n)
    {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;
    }
}

if (! function_exists('alpha2num')) {
    function alpha2num($a)
    {
        $r = 0;
        $l = strlen($a);
            for ($i = 0; $i < $l; $i++) {
            $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
        }
        return $r - 1;
    }
}

if (!function_exists('csp_nonce')) {
    /**
     * This helper function makes it easier to generate
     * nonce for inline scripts and styles in views.
     *
     * @param string $target
     *
     * @return string
     *
     * @throws Exception
     */
    function csp_nonce(string $target = 'script'): string
    {
        return SecureHeaders::nonce($target);
    }
}

if (! function_exists('hashids')) {
    /**
     * Call Hasids methods
     *
     * @param  $salt  string
     * @return \App\Services\Encrypter\Hashids
     */
    function hashids($salt = null): Hashids
    {
        return new Hashids($salt);
    }
}

if (! function_exists('dms2dec')) {
    /*
    * Convert DMS (degrees / minutes / seconds) to decimal degrees
    *
    * Todd Trann
    * May 22, 2015
    */
    function dms2dec($latlng)
    {
        $valid = false;
        $decimal_degrees = 0;
        $degrees = 0;
        $minutes = 0;
        $seconds = 0;
        $direction = 1;

        // Determine if there are extra periods in the input string
        $num_periods = substr_count($latlng, '.');
        if ($num_periods > 1) {
            $temp = preg_replace('/\./', ' ', $latlng, $num_periods - 1); // replace all but last period with delimiter
            $temp = trim(preg_replace('/[a-zA-Z]/', '', $temp)); // when counting chunks we only want numbers
            $chunk_count = count(explode(" ", $temp));
            if ($chunk_count > 2) {
                $latlng = preg_replace('/\./', ' ', $latlng, $num_periods - 1); // remove last period
            } else {
                $latlng = str_replace(".", " ", $latlng); // remove all periods, not enough chunks left by keeping last one
            }
        }

        // Remove unneeded characters
        $latlng = trim($latlng);
        $latlng = str_replace("º", " ", $latlng);
        $latlng = str_replace("°", " ", $latlng);
        $latlng = str_replace("'", " ", $latlng);
        $latlng = str_replace("\"", " ", $latlng);
        $latlng = str_replace("  ", " ", $latlng);
        $latlng = substr($latlng, 0, 1) . str_replace('-', ' ', substr($latlng, 1)); // remove all but first dash

        if ($latlng != "") {
            // DMS with the direction at the start of the string
            if (preg_match("/^([nsewoNSEWO]?)\s*(\d{1,3})\s+(\d{1,3})\s*(\d*\.?\d*)$/", $latlng, $matches)) {
                $valid = true;
                $degrees = intval($matches[2]);
                $minutes = intval($matches[3]);
                $seconds = floatval($matches[4]);
                if (strtoupper($matches[1]) == "S" || strtoupper($matches[1]) == "W")
                    $direction = -1;
            }
            // DMS with the direction at the end of the string
            elseif (preg_match("/^(-?\d{1,3})\s+(\d{1,3})\s*(\d*(?:\.\d*)?)\s*([nsewoNSEWO]?)$/", $latlng, $matches)) {
                $valid = true;
                $degrees = intval($matches[1]);
                $minutes = intval($matches[2]);
                $seconds = floatval($matches[3]);
                if (strtoupper($matches[4]) == "S" || strtoupper($matches[4]) == "W" || $degrees < 0) {
                    $direction = -1;
                    $degrees = abs($degrees);
                }
            }
            if ($valid) {
                // A match was found, do the calculation
                $decimal_degrees = ($degrees + ($minutes / 60) + ($seconds / 3600)) * $direction;
            } else {
                // Decimal degrees with a direction at the start of the string
                if (preg_match("/^([nsewNSEW]?)\s*(\d+(?:\.\d+)?)$/", $latlng, $matches)) {
                    $valid = true;
                    if (strtoupper($matches[1]) == "S" || strtoupper($matches[1]) == "W")
                        $direction = -1;
                    $decimal_degrees = $matches[2] * $direction;
                }
                // Decimal degrees with a direction at the end of the string
                elseif (preg_match("/^(-?\d+(?:\.\d+)?)\s*([nsewNSEW]?)$/", $latlng, $matches)) {
                    $valid = true;
                    if (strtoupper($matches[2]) == "S" || strtoupper($matches[2]) == "W" || $degrees < 0) {
                        $direction = -1;
                        $degrees = abs($degrees);
                    }
                    $decimal_degrees = $matches[1] * $direction;
                }
            }
        }
        if ($valid) {
            return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $decimal_degrees);
        } else {
            return false;
        }
    }
}


if (! function_exists('calculateVincentyGreatCircleDistance')) {
    /**
     * Calculate distance between two coordinates.
     *
     * @param string|float $latitudeFrom
     * @param string|float $longitudeFrom
     * @param string|float $latitudeTo
     * @param string|float $longitudeTo
     * @param int          $earthRadius
     *
     * @return float
     */
    function calculateVincentyGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $figureA = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $figureB = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($figureA), $figureB);

        return $angle * $earthRadius;
    }
}

if (! function_exists('sanitize_filename')) {
    /**
     * Sanitize string for filename
     *
     * @return string
     */
    function sanitize_filename(string $str, string $replaceWith = ''): string
    {
        return preg_replace('/[^a-zA-Z0-9\s]+/', $replaceWith, $str);
    }
}

if (! function_exists('normalize_phone_number')) {
    /**
     * Normalize phone number when filled with leading zero
     *
     * @return string
     */
    function normalize_phone_number($phoneNumber): string
    {
        if (empty($phoneNumber)) {
            return $phoneNumber;
        }

        $phoneNumber = (string) $phoneNumber;

        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '63' . substr($phoneNumber, 1);
        }

        if (substr($phoneNumber, 0, 2) !== '63') {
            return null;
        }

        return $phoneNumber;
    }
}

if (! function_exists('available_file_type_group')) {
    /**
     * Guess custom file type group from an extension
     *
     * @return array
     */
    function available_file_type_group(): array
    {
        return [
            'image' => ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'svg', 'webp', 'bmp', 'ico', 'avif', 'tif', 'tiff', 'cur', 'wmf', 'emf'],
            'video' => ['mpg', 'mpeg', 'mp4', 'webm', 'mov', 'avi', 'wmv', 'swf', 'flv', '3gp', 'qt', 'm4p', 'mp2'],
            'audio' => ['mp3', 'ogg', 'wav', 'pcm', 'aac', 'opus'],
            'excel' => ['xlsx', 'xls', 'xlsm', 'xlsb', 'xltx', 'xltm', 'xlt', 'xml', 'xlam', 'xlc', 'xlt', 'xld', 'xlk', 'xla','xlw', 'xlr', 'ods'],
            'csv' => ['csv'],
            'pdf' => ['pdf'],
            'doc' => ['doc', 'docx', 'docm'],
            'ppt' => ['ppt', 'pptx', 'pptm'],
            'zip' => ['zip', 'tar', 'tar.gz', 'bz', 'bz2', 'gz', '7z', 'tgz'],
            'rar' => ['rar'],
            'text' => ['txt', 'prn', 'dif'],
        ];
    }
}

if (! function_exists('guess_file_type_group')) {
    /**
     * Guess custom file type group from an extension
     *
     * @return ?string
     */
    function guess_file_type_group($extension): ?string
    {
        foreach (available_file_type_group() as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return null;
    }
}

if (! function_exists('partially_hide_email')) {
    function partially_hide_email(string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            list($first, $last) = explode('@', $email);

            $firstLen = floor(strlen($first)/2);
            $first = str_replace(substr($first, $firstLen), str_repeat('*', strlen($first) - $firstLen), $first);

            $lastIndex = strpos($last, '.');
            $last1 = substr($last, 0, $lastIndex);
            $last2 = substr($last, $lastIndex);
            $lastLen  = floor(strlen($last1)/2);

            $last1 = str_replace(substr($last1, $lastLen), str_repeat('*', strlen($last1) - $lastLen), $last1);
            $partiallyHideEmail = $first.'@'.$last1.''.$last2;

            return $partiallyHideEmail;
        }

        return '';
    }
}

if (! function_exists('mute_exception')) {
    function mute_exception(Closure $fn, $default = null)
    {
        try {
            return $fn();
        } catch (Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('objectify')) {
    function objectify(array|object $data = [])
    {
        return new class($data) extends \ArrayObject {
            public function __get($key)
            {
                return $this->offsetGet($key) ?? null;
            }

            public function __set($key, $value)
            {
                return $this->offsetSet($key, $value);
            }
        };
    }
}

if (! function_exists('http_encrypt')) {
    function http_encrypt(string $payload): string
    {
        return app(HttpEncrypter::class)->encrypt($payload);
    }
}

if (! function_exists('http_decrypt')) {
    function http_decrypt(string $payload): string
    {
        return app(HttpEncrypter::class)->decrypt($payload);
    }
}

if (! function_exists('trx_encrypt')) {
    function trx_encrypt(string $payload): string
    {
        return app(TrxCrypter::class)->encrypt($payload);
    }
}

if (! function_exists('trx_decrypt')) {
    function trx_decrypt(string $payload, bool $checkExpiry = false): string
    {
        return app(TrxCrypter::class)->decrypt($payload, $checkExpiry);
    }
}

if (! function_exists('acceptable_to_boolean')) {
    function acceptable_to_boolean(mixed $acceptable): bool
    {
        if (empty($acceptable)) {
            return false;
        }

        if (is_string($acceptable)) {
            $acceptable = strtolower($acceptable);
        }

        $accepts = [
            'yes', 'on', '1', 1, true, 'true', 'y',
        ];

        if (in_array($acceptable, $accepts, true)) {
            return true;
        }

        $declines = [
            'no', 'off', '0', 0, false, 'false', 'n',
        ];

        return in_array($acceptable, $declines, true);
    }
}



if (! function_exists('acceptable_to_boolean')) {
    function acceptable_to_boolean($acceptable): bool
    {
        if (empty($acceptable)) {
            return false;
        }

        if (is_string($acceptable)) {
            $acceptable = strtolower($acceptable);
        }

        $accepts = [
            'yes', 'on', '1', 1, true, 'true', 'y',
        ];

        if (in_array($acceptable, $accepts, true)) {
            return true;
        }

        $declines = [
            'no', 'off', '0', 0, false, 'false', 'n',
        ];

        return in_array($acceptable, $declines, true);
    }
}

if (! function_exists('quasar_asset')) {
    function quasar_asset($filename) {
        if (substr($filename, 0, 1) !== '/') {
            $filename = '/' . $filename;
        }

        if (app()->isProduction()) {
            $manifest = json_decode(file_get_contents(public_path('quasar-manifest.json')), true);

            return isset($manifest[$filename]) ? $manifest[$filename] : '';
        }

        if (@file_get_contents('http://localhost:' . config('app.dev_server_port') . '/sockjs-node/info?t=' . ((int) microtime(true)))) {
            $filename = str_replace(['js/', 'css/'], '', $filename);

            return '//localhost:' . config('app.dev_server_port') . $filename;
        }

        $manifest = json_decode(file_get_contents(public_path('quasar-manifest.json')), true);

        return isset($manifest[$filename]) ? $manifest[$filename] : '';
    }
}

if (! function_exists('format_seconds_to_time')) {
    function format_seconds_to_time($seconds, $withDay = false, $withSeconds = false) {
        if (empty($seconds)) {
            if ($withSeconds) {
                return '00:00:00';
            }

            return '00:00';
        }

        $days = null;
        $hours = null;

        if ($withDay) {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
        } else {
            $days = 0;
            $hours = floor($seconds / 3600);
        }

        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $formattedTime = sprintf('%02d:%02d', $hours, $minutes);

        if ($withSeconds) {
            $formattedTime .= sprintf(':%02d', $remainingSeconds);
        }

        if ($withDay && $days) {
            return sprintf('%dd %s', $days, $formattedTime);
        }

        return $formattedTime;
    }
}


if (! function_exists('pembilang')) {
    function pembilang($value) {
        $value = floor(abs($value));

        $huruf = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas',
        ];
        $temp = '';

        if ($value < 12) {
            return ' ' . $huruf[$value];
        } else if ($value < 20) {
            return pembilang($value - 10) . ' belas';
        } else if ($value < 100) {
            $calculated = floor($value / 10);
            return pembilang($calculated) . ' puluh' . pembilang($value % 10);
        } else if ($value < 200) {
            return ' seratus' . pembilang($value - 100);
        } else if ($value < 1000) {
            $calculated = floor($value / 100);
            return pembilang($calculated) . ' ratus' . pembilang($value % 100);
        } else if ($value < 2000) {
            return ' seribu' . pembilang($value - 1000);
        } else if ($value < 1000000) {
            $calculated = floor($value / 1000);
            return pembilang($calculated) . ' ribu' . pembilang($value % 1000);
        } else if ($value < 1000000000) {
            $calculated = floor($value / 1000000);
            return pembilang($calculated) . ' juta' . pembilang($value % 1000000);
        } else if ($value < 1000000000000) {
            $calculated = floor($value / 1000000000);
            return pembilang($calculated) . ' miliar' . pembilang($value % 1000000000);
        } else if ($value < 1000000000000000) {
            $calculated = floor($value / 1000000000000);
            return pembilang($value / 1000000000000) . ' triliun' . pembilang($value % 1000000000000);
        }

        return trim($temp);
    }
}

if (! function_exists('terbilang')) {
    function terbilang($number) {
        $result = pembilang(round($number));

        return $result ? ($result . ' rupiah') : '';
    }
}

if (! function_exists('format_npwp')) {
    function format_npwp($npwp) {
        $npwp = preg_replace('/\D/', '', (string) $npwp);
        $npwp = str_pad($npwp, 15, '0', STR_PAD_LEFT);
        return substr($npwp, 0, 2) . '.' .
                substr($npwp, 2, 3) . '.' .
                substr($npwp, 5, 3) . '.' .
                substr($npwp, 8, 1) . '-' .
                substr($npwp, 9, 3) . '.' .
                substr($npwp, 12, 3);
    }
}
