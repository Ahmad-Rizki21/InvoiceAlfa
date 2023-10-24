<?php

namespace App\Services\Auth;

use App\Models\DistributionCenterAccessToken;
use App\Models\FranchiseAccessToken;
use App\Models\UserAccessToken;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Laravel\Sanctum\Events\TokenAuthenticated;
use App\Services\Auth\Contracts\AuthenticatableUser;
use Carbon\Carbon;

class CustomGuard
{
    /**
     * The authentication factory implementation.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The number of minutes tokens should be allowed to remain valid.
     *
     * @var int
     */
    protected $expiration;

    /**
     * The provider name.
     *
     * @var string
     */
    protected $provider;

    /**
     * Create a new guard instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @param  int  $expiration
     * @param  string  $provider
     * @param  string  $driver
     * @return void
     */
    public function __construct(AuthFactory $auth, $expiration = null, $provider = null)
    {
        $this->auth = $auth;
        $this->expiration = $expiration;
        $this->provider = $provider;
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        if ($token = $this->getTokenFromRequest($request)) {
            $tokenIdentifier = substr($token, 0, 3);

            foreach ([UserAccessToken::class, DistributionCenterAccessToken::class, FranchiseAccessToken::class] as $accessTokenClass) {
                if ($tokenIdentifier === ($accessTokenClass::tokenPrefix() . ':e')) {
                    return $this->findUser($request, $token, $accessTokenClass);
                }
            }
        }
    }

    protected function findUser(Request $request, $token, $accessTokenClass)
    {
        $ipAddress = $request->ip();

        $accessToken = $accessTokenClass::findToken($token);

        if (
            ! $this->isValidAccessToken($accessToken) ||
            ! $this->supportsTokens($accessToken->user)
        ) {
            return;
        }

        if (! $accessToken->last_used_at || Carbon::now()->subMinutes(2)->lt($accessToken->last_used_at)) {
            $accessToken->forceFill(['last_used_at' => Carbon::now()]);

            if ($ipAddress && $ipAddress != $accessToken->ip_address) {
                $accessToken->forceFill(['ip_address' => $ipAddress]);
            }

            $accessToken->save();
        } else if ($ipAddress && $ipAddress != $accessToken->ip_address) {
            $accessToken->forceFill(['ip_address' => $ipAddress])->save();
        }

        $user = $accessToken->user->withAccessToken($accessToken);

        event(new TokenAuthenticated($accessToken));

        return $user;
    }

    /**
     * Determine if the tokenable model supports API tokens.
     *
     * @param  mixed  $tokenable
     * @return bool
     */
    protected function supportsTokens($tokenable = null)
    {
        return $tokenable instanceof AuthenticatableUser;
    }

    /**
     * Get the token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request)
    {
        return $request->bearerToken();
    }

    /**
     * Determine if the provided access token is valid.
     *
     * @param  mixed  $accessToken
     * @return bool
     */
    protected function isValidAccessToken($accessToken): bool
    {
        if (! $accessToken) {
            return false;
        }

        return ! $this->expiration || $accessToken->created_at->gt(Carbon::now()->subMinutes($this->expiration));
    }
}
