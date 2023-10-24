<?php

namespace App\Http\Middleware;

use App\Service\Jwt\Parser as JwtParser;
use Laravel\Passport\Token as AccessToken;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\AuthenticationException;

class AuthenticateExport
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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
        $token = $request->bearerToken() ?: null;

        if (! $token) {
            $token = $request->query('api_token');
        }

        if (! $token) {
            $token = $request->cookie('api_token');
        }

        if (! $token) {
            abort(403, 'Forbidden');
        }

        if (! $request->bearerToken()) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        $guard = 'api';

        if ($this->auth->guard($guard)->check()) {
            $this->auth->shouldUse($guard);

            return $next($request);
        }

        throw new AuthenticationException('Unauthenticated.', [$guard]);
    }
}
