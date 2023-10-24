<?php

namespace App\Http\Controllers;

use App\Models\AccessToken\AccessToken;
use App\Models\Role;
use App\Services\Encrypter\Hashids;
use App\Services\UrlQuery\UrlQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\{
    Auth,
    File,
    Request as RequestFacade
};

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Construct a UrlQuery class
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Services\UrlQuery\UrlQuery
     */
    public function urlQuery(Request $request)
    {
        return new UrlQuery($request);
    }

    protected function json(array $response, $httpStatus = 200, array $headers = [])
    {
        $response = array_merge([
            'status' => 'success',
            'message' => __('Ok'),
            'code' => 0,
            'data' => new \stdClass(),
            'meta' => new \stdClass(),
        ], $response);

        return response()->json($response, $httpStatus, $headers);
    }

    protected function sendFile($filepath, $httpStatus = 200, array $headers = [])
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }

        $cacheTime = 120; // 2 minutes

        if (!file_exists($filepath)) {
            return abort(404);
        }

        ini_set('zlib.output_compression', 'Off');

        $relativeFilepath = ltrim(ltrim(str_replace(storage_path('app'), '', $filepath), '/'), '\\');
        $responseSize = filesize($filepath);
        $filename = File::basename($filepath);

        $loweredHeaders = [];

        foreach ($headers as $key => $value) {
            $loweredHeaders[strtolower($key)] - $value;
        }

        if (!isset($loweredHeaders['cache-control'])) {
            $headers['Cache-Control'] = "public, max-age={$cacheTime}, pre-check={$cacheTime}";
        }

        if (!isset($loweredHeaders['expires'])) {
            $headers['Expires'] = gmdate(DATE_RFC1123, time() + $cacheTime);
        }

        if (!isset($loweredHeaders['last-modified'])) {
            $headers['Expires'] = gmdate(DATE_RFC1123, File::lastModified($filepath));
        }

        if (!isset($loweredHeaders['content-disposition'])) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
        }

        if (!isset($loweredHeaders['content-length'])) {
            $headers['Content-Length'] = $responseSize;
        }

        if (!isset($loweredHeaders['content-type'])) {
            $headers['Content-Type'] = File::mimeType($filepath);
        }

        if (!isset($loweredHeaders['filename'])) {
            $headers['Filename'] = urldecode($filename);
        }

        $xSendFile = false;

        // APACHE
        // Relies on the mod_xsendfile module.
        if (function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules())) {
            $xSendFile = true;
            $headers['X-Sendfile'] = $filepath;
        }
        // LIGHTTPD
        else if (isset($_SERVER['SERVER_SOFTWARE']) && preg_match("/lighttpd\/1(\.[0-9]+)+/", $_SERVER['SERVER_SOFTWARE'])) {
            $xSendFile = true;
            $headers['X-LIGHTTPD-send-file'] = $filepath;
        }
        // NGINX (default)
        else {
            $xSendFile = true;
            $headers['X-Accel-Redirect'] = "/{$relativeFilepath}";
        }

        $responseStart = 0;
        $responseEnd = $responseSize - 1;
        $videoMimeTypes = [
            'video/3gp',
            'video/webm',
            'video/mp4',
            'video/ogg',
            'video/x-flv',
            'video/x-matroska',
        ];

        $isMedia = in_array($headers['Content-Type'], $videoMimeTypes);

        if ($isMedia) {
            $headers['Accept-Ranges'] = '0-' . ($responseSize - 1);

            if (isset($_SERVER['HTTP_RANGE'])) {
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                if (strpos($range, ',') !== false) {
                    return response('Requested Range Not Satisfiable', 416, [
                        'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
                    ]);
                }

                if ($range == '-') {
                    $responseStart = $responseSize - substr($range, 1);
                } else {
                    $range = explode('-', $range);
                    $responseStart = $range[0];

                    $responseEnd = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $responseEnd;
                }

                if ($responseStart > $responseEnd || $responseStart > $responseSize - 1 || $responseEnd >= $responseSize) {
                    return response('Requested Range Not Satisfiable', 416, [
                        'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
                    ]);
                }

                $httpStatus = 206;
                $headers['Content-Length'] = $responseSize - $responseStart;
                $headers['Content-Range'] = "bytes {$responseStart}-{$responseEnd}/{$responseSize}";

                unset($headers['Accept-Ranges']);
                unset($headers['Cache-Control']);
                unset($headers['Content-Disposition']);
                unset($headers['Expires']);
            }
        }

        if ($xSendFile) {
            return response('', $httpStatus, $headers);
        }

        if ($isMedia) {
            return response()->stream(function () use ($filepath, $responseStart, $responseEnd) {
                if (!($responseStream = fopen($filepath, 'rb'))) {
                    abort(500, 'Could not open requested file.');
                }

                if ($responseStart > 0) {
                    fseek($responseStream, $responseStart);
                }

                $streamCurrent = 0;

                while (!feof($responseStream) && $streamCurrent < $responseEnd && connection_status() == 0) {
                    echo fread($responseStream, min(1024 * 16, $responseEnd - $responseStart + 1));
                    $streamCurrent += 1024 * 16;
                }

                fclose($responseStream);
            }, $httpStatus, $headers);
        }

        return response()->download($filepath, $filename, $headers);
    }

    // public function isFromConsole()
    // {
    //     $user = Auth::guard('api')->user();

    //     if (!$user) {
    //         $channelKey = request()->channel_key ?: request()->header('x-channel-key');

    //         if ($channelKey) {
    //             return Hashids::decodeAuthChannelKey($channelKey) == AccessToken::CHANNEL_CONSOLE;
    //         }

    //         return false;
    //     }

    //     return true;

    //     return $user->currentAccessToken()->channel == AccessToken::CHANNEL_CONSOLE;
    // }

    public function isLoggedIn()
    {
        return Auth::guard('api')->check();
    }

    public function isSuperAdmin()
    {
        return $this->isAuthRole(Role::TYPE_SUPER_ADMIN);
    }

    public function isAuthRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        return in_array($user->role_id, $roles);
    }
}
