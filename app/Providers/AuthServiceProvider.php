<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('permissions')) {
                Permission::getCached()->each(function ($permission) {
                    Gate::define($permission->key, function ($user) use ($permission) {
                        if (! $user || ! $user->role_id) {
                            return false;
                        }

                        if ($user->role_id == Role::TYPE_SUPER_ADMIN) {
                            return true;
                        }

                        return $user->hasPermission($permission->key);
                    });
                });
            }
        } catch (Throwable $e) {
            Gate::before(function ($user, $ability) {
                if (! $user || ! $user->role_id) {
                    return false;
                }

                if ($user->role_id == Role::TYPE_SUPER_ADMIN) {
                    return true;
                }

                return false;
            });
        }
    }
}
