<?php

namespace App\Models;

use App\Services\Auth\Contracts\AuthenticatableUser;
use App\Services\Database\Eloquent\User as BaseUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends BaseUser implements AuthenticatableUser
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'email_verified_at',
        'status', 'role_id', 'locale',
    ];

    public function tokens()
    {
        return $this->hasMany(UserAccessToken::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function getPermissionsAttribute()
    {
        if (($role = $this->role)) {
            return  $role->getCachedPermissionKeys();
        }

        return [];
    }

    public function hasPermission($permission)
    {
        $availablePermissions = $this->getPermissionsAttribute();

        if (!$availablePermissions || !count($availablePermissions)) {
            return false;
        }

        if (!is_array($permission)) {
            $permission = [$permission];
        }

        foreach ($permission as $value) {
            if (in_array($value, $availablePermissions)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole($roleId)
    {
        if ($role = $this->role) {
            return $role->id == $roleId;
        }

        return false;
    }

    public function getEncodedPermissionsAttribute()
    {
        if ($this->role_id == Role::TYPE_SUPER_ADMIN) {
            return [];
        }

        return $this->getPermissionsAttribute();
    }
}
