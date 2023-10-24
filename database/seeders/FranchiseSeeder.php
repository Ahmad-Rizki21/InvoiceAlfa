<?php

namespace Database\Seeders;

use App\Models\DistributionCenter;
use App\Models\Franchise;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FranchiseSeeder extends Seeder
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
        Franchise::truncate();

        $firstDistributionCenter = DistributionCenter::first();

        $data = [
            [
                'name' => 'Test Alfa Franchise',
                'location' => 'Tangerang',
                'distribution_center_id' => $firstDistributionCenter->id,
            ],
        ];

        foreach ($data as $value) {
            Franchise::create($value);
        }
    }
}
