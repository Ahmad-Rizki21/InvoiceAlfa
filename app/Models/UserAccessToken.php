<?php

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Contracts\HasAbilities;

class UserAccessToken extends Model implements HasAbilities
{
    use HasUlids;

    protected $table = 'user_access_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'token', 'abilities', 'client_code',
        'user_agent', 'ip_address', 'revoked',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    public const CLIENT_CONSOLE = 1;
    public const ALL_CHANNELS = [
        self::CLIENT_CONSOLE,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'user');
    }

    public static function tokenPrefix()
    {
        return 'u';
    }

    public function getExpiresAtAttribute()
    {
        $expiration = config('sanctum.expiration');

        if (! $expiration) {
            return null;
        }

        return $this->created_at->addMinutes($expiration);
    }

    public function getTokenHashAttribute()
    {
        return static::tokenPrefix() . ':'.encrypt($this->getKey() . '|' . $this->token);
    }
    /**
     * Find the token instance matching the given token.
     *
     * @param  string  $token
     * @return static|null
     */
    public static function findToken($token)
    {
        if (substr($token, 0, 3) === static::tokenPrefix() . ':e') {
            $token = decrypt(substr($token, 2));
        }

        [$id, $token] = explode('|', $token, 2);

        return static::where('id', $id)->where('token', $token)->where('revoked', 0)->first();
    }

    public static function revokeAllTokenForUser(User $user, self $unless = null)
    {
        DB::transaction(function () use ($user, $unless) {
            $tokens = static::where('user_id', $user->id);

            if ($unless) {
                $tokens = $tokens->where('id', '!=', $unless->id)
                    ->where(function ($q) use ($unless) {
                        $q->where('ip', $unless->ip)
                            ->where('user_agent', $unless->user_agent);
                    });
            }

            $tokens = $tokens->get();

            foreach ($tokens as $token) {
                $token->revoked = 1;
                $token->save();
            }
        });
    }

    /**
     * Determine if the token has a given ability.
     *
     * @param  string  $ability
     * @return bool
     */
    public function can($ability)
    {
        return in_array('*', $this->abilities) ||
               array_key_exists($ability, array_flip($this->abilities));
    }

    /**
     * Determine if the token is missing a given ability.
     *
     * @param  string  $ability
     * @return bool
     */
    public function cant($ability)
    {
        return ! $this->can($ability);
    }
}
