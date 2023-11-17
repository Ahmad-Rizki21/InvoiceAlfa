<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CaptureQuery
{
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
        $this->enabled = (bool) config('app.log_query');

        if ($this->enabled) {
            DB::enableQueryLog();
        }
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
        $response = $next($request);


        if ($this->enabled) {
            $queryLog = DB::getQueryLog();
            $logs = [];

            foreach ($queryLog as $query) {
                $rawSql = $query['query'];
                $bindings = $query['bindings'];
                $logs[] = [vsprintf(str_replace('?', '%s', $rawSql), $bindings), $query['time']];
            }

            if (count($logs)) {
                Log::channel('query')->info('query', [
                    'query' => $logs,
                ]);
            }
        }

        return $response;
    }

    // public function terminate($request, $response)
    // {

    //     return $response;
    // }
}
