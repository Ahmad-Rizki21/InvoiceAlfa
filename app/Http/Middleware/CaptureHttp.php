<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CaptureHttp
{
    /**
     * The ID of current transports.
     *
     * @var string
     */
    protected $transportId;

    /**
     * Enable logging.
     *
     * @var string
     */
    protected $enabled;

    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->transportId = (string) Str::ulid();
        $this->enabled = (bool) config('app.log_http');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if ($this->enabled) {
            $res = $response->getContent();

            if (is_string($res) && strlen($res) > 10000) {
                $res = substr($res, 0, 100) . ' too long...';
            } else {
                try {
                    $result = json_decode($res, true);

                    if ($result && is_array($result)) {
                        $res = $result;
                    }

                    try {
                        if (is_array($res)) {
                            $r = trim(preg_replace('/\\\n/', ' ', json_encode($res)));
                            $res = json_decode($r);
                        }
                    } catch (\Throwable $e) {
                        //
                    }
                } catch (\Throwable $e) {
                    //
                }
            }

            Log::channel('http')->info('http', [
                'id' => $this->transportId,
                'user_id' => Auth::id(),
                'status' => $response->getStatusCode(),
                'url' => $request->url(),
                'method' => $request->getMethod(),
                'req' => $request->all(),
                'res' => $res,
                'referer' => $request->header('referer'),
                'time_taken' => defined('LARAVEL_START') ? (microtime(true) - LARAVEL_START) : null,
            ]);
        }

        return $response;
    }
}
