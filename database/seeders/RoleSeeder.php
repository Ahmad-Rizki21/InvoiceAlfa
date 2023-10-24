<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

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
        Role::flushCached();
        Role::truncate();

        $data = [
            [
                'id' => Role::TYPE_SUPER_ADMIN,
                'name' => 'Super Admin',
                'description' => 'Manage application',
            ],
        ];

        foreach ($data as $value) {
            $data = new Role($value);
            $data->id = $value['id'];
            $data->save();
        }
    }
}
