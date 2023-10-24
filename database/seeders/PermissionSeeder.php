<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    protected $permissionIds = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        Schema::disableForeignKeyConstraints();

        $this->seedEntries();

        Schema::enableForeignKeyConstraints();
        Model::reguard();
    }

    protected function seedEntries()
    {
        DB::table('role_permission')->truncate();
        Permission::flushCached();
        Role::flushCached();
        Permission::truncate();

        $permissions = [
            'Dashboard' => [
                [
                    'key' => 'access.dashboard',
                    'name' => 'Access (and login to) console dashboard',
                ],
            ],

            'Users' => [
                [
                    'key' => 'manage.users',
                    'name' => 'Access users management page',
                ],
                [
                    'key' => 'create.users',
                    'name' => 'Create new user',
                ],
                [
                    'key' => 'edit.users',
                    'name' => 'Edit user',
                ],
                [
                    'key' => 'delete.users',
                    'name' => 'Delete user',
                ],
            ],

            'User Access' => [
                [
                    'key' => 'manage.permission',
                    'name' => 'Access user access page',
                ],
                [
                    'key' => 'create.role',
                    'name' => 'Create new role',
                ],
                [
                    'key' => 'edit.role',
                    'name' => 'Edit role',
                ],
                [
                    'key' => 'delete.role',
                    'name' => 'Delete role',
                ],
                [
                    'key' => 'edit.permission',
                    'name' => 'Edit role permission',
                ],
            ],
        ];

        // ksort($permissions);

        $data = new Collection();
        $permissionIds = [];

        foreach ($permissions as $module => $perms) {
            foreach ($perms as $perm) {
                $permission = Permission::create(array_merge(['module' => $module], $perm));
                $data[] = $permission;

                $permissionIds[$permission->key] = $permission->id;
            }
        }

        $this->permissionIds = $permissionIds;


        $this->seedRolePermissions(Role::TYPE_SUPER_ADMIN, [
            'access.dashboard',

            'manage.users',
            'create.users',
            'edit.users',
            'delete.users',

            'create.role',
            'edit.role',
            'delete.role',
            'manage.permission',
            'edit.permission',
        ]);

        foreach (Role::where('id', '!=', Role::TYPE_SUPER_ADMIN)->get() as $role) {
            $this->seedRolePermissions($role->id, [
                'access.dashboard',

                // 'manage.users',
                // 'create.users',
                // 'edit.users',
                // 'delete.users',

                // 'create.role',
                // 'edit.role',
                // 'delete.role',
                // 'manage.permission',
                // 'edit.permission',
            ]);
        }
    }

    protected function seedRolePermissions($roleType, $permissions)
    {
        $permissionsIds = [];

        foreach ($permissions as $key) {
            if (isset($this->permissionIds[$key])) {
                $permissionsIds[] = $this->permissionIds[$key];
            }
        }

        Role::find($roleType)->permissions()->sync($permissionsIds);
    }
}
