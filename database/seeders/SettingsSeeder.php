<?php

namespace Database\Seeders;

use App\Enums\SettingKey;
use App\Models\DistributionCenter;
use App\Models\Settings;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SettingsSeeder extends Seeder
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
        Settings::truncate();

        $data = [
            [
                'key' => SettingKey::StampDuty->value,
                'value' => null,
            ],
            [
                'key' => SettingKey::PpnPercentage->value,
                'value' => 11,
            ],
            [
                'key' => SettingKey::InvoiceNote->value,
                'value' => 'Keterlambatan Pembayaran menyebabkan ketidaknyamanan Pelayanan Jaringan ke Pelanggan',
            ],
            [
                'key' => SettingKey::SignatoryName->value,
                'value' => 'Nazirin Nawawi',
            ],
            [
                'key' => SettingKey::SignatoryPosition->value,
                'value' => 'Head Finance & Administrasi',
            ],
            [
                'key' => SettingKey::BankTransferName->value,
                'value' => 'BANK CENTRAL ASIA - KCU KALIMALANG JAKARTA',
            ],
            [
                'key' => SettingKey::BankTransferAccountNumber->value,
                'value' => '2306079899',
            ],
            [
                'key' => SettingKey::BankTransferAccountName->value,
                'value' => 'PT.ARTACOMINDO JEJARING NUSA',
            ],
            [
                'key' => SettingKey::SignatureImage->value,
                'value' => '',
            ],
            [
                'key' => SettingKey::StampImage->value,
                'value' => '',
            ],
            [
                'key' => SettingKey::InjectInvoiceNo->value,
                'value' => '',
            ],
        ];

        foreach ($data as $value) {
            Settings::create($value);
        }
    }
}
