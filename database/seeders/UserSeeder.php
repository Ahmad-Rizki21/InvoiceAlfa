<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
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
        User::truncate();

        $data = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@gmail.com',
                'username' => 'superadmin',
                'password' => '$2y$10$xx/N7p0mCBEP2fMQpUdWtuNR0pG9JtvgaKIIvu2iVLOYQHUQnnauK',
                'email_verified_at' => Carbon::now(),
                'role_id' => Role::TYPE_SUPER_ADMIN,
            ],
        ];

        foreach ($data as $value) {
            User::create($value);
        }
    }
}
