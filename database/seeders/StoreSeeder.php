<?php

namespace Database\Seeders;

use App\Models\DistributionCenter;
use App\Models\Store;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StoreSeeder extends Seeder
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
        Store::truncate();

        $firstDistributionCenter = DistributionCenter::first();

        $data = [
            [
                'name' => 'Test Alfa Store',
                'location' => 'Tangerang',
                'distribution_center_id' => $firstDistributionCenter->id,
            ],
        ];

        foreach ($data as $value) {
            Store::create($value);
        }
    }
}
