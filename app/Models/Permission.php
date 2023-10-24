<?php

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'key', 'name', 'module', 'guard',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($instance) {
            $instance->roles()->delete();
        });

        static::deleted(function () {
            static::flushCached();
        });
    }

    public function roles()
    {
        return $this->belongsToMany(
                Role::class,
                'role_permission',
                'permission_id',
                'role_id',
                'id',
                'id'
            );
    }

    public static function getCached()
    {
        return Cache::rememberForever(static::class, function () {
            return Permission::all();
        });
    }

    public static function flushCached()
    {
        Cache::forget(static::class);
    }
}
