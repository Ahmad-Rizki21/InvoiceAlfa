<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\CandidateOwner;
use App\Services\Database\Contracts\CacheableModel;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Exception;

class Role extends Model implements CacheableModel
{
    use SoftDeletes;

    public const TYPE_SUPER_ADMIN = 1;
    public const ALL_TYPES = [
        self::TYPE_SUPER_ADMIN,
    ];

    protected $table = 'roles';

    protected $fillable = [
        'name', 'description', 'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($instance) {
            if ($instance->isForceDeleting()) {
                if ($instance->users()->count()) {
                    throw new Exception(
                        'There are users still relating to ' . $instance->name .
                        ' role. Please detach them first.'
                    );
                }
                $instance->permissions()->sync([]);
            }
        });

        static::saving(function () {
            Cache::forget(static::class);
        });

        static::deleted(function () {
            static::flushCached();
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id', 'users');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id',
            'id',
            'id'
        );
    }

    public function getCachedPermissionKeys()
    {
        return Cache::remember(Permission::class.'role_'.$this->id, 86400, function () {
            $permissions = $this->permissions();

            return $permissions->get(['key'])->pluck('key')->toArray();
        });
    }

    public function flushCachedPermissionKeys(): bool
    {
        return Cache::forget(Permission::class.'role_'.$this->id);
    }

    public static function getCached()
    {
        return Cache::rememberForever(static::class, function () {
            return static::get();
        });
    }

    public static function flushCached()
    {
        Cache::forget(static::class);

        static::all()->map(function ($item) {
            Cache::forget(Permission::class.'role_'.$item->id);
        });
    }
}
