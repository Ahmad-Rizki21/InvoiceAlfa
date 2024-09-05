<?php

namespace Database\Seeders;

use App\Models\DistributionCenter;
use App\Models\Store;
use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceNoSeeder extends Seeder
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

        DB::table('invoices')->update([
            'no' => DB::raw("regexp_replace(invoice_no, '([0-9]+)/.+', '$1')")
        ]);
    }
}
