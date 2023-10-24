<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Services\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DistributionCenterSeeder extends Seeder
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
        DistributionCenter::truncate();

        $data = [
            [
                'name' => 'PT. SUMBER ALFARIA TRIJAYA, Tbk Branch Balaraja',
                'address' => 'Jl. Arya Jaya Santika No. 19, Kp. Seglok Desa Pasir Bolang Kec. Tigaraksa Kab. Tangerang',
                'offering_letter_reference_number' => '013/ARTACOM-SAT/SALES/2023',
                'fo_offering_letter_reference_number' => '030/ARTACOM-SAT/SALES/IV/2021',
                'approval_date' => '2021-04-05',
                'fo_approval_date' => '2022-11-22',
                'email' => 'demodc@gmail.com',
                'fo_issuance_number' => 'SAT-ARTACOM/IT/KONEKSI/X1/2022/CMII-339 tanggal 22',
                'username' => 'demodc',
                'password' => '$2y$10$GN7SC5Lyd50OszAbwJSecO5IMsLwtDRQGq22YNNUcCqf1i7cbGgW.',
                'email_verified_at' => Carbon::now(),
            ],
        ];

        foreach ($data as $value) {
            DistributionCenter::create($value);
        }
    }
}
