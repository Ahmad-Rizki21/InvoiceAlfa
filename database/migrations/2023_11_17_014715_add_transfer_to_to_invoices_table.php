<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedInteger('transfer_to_type')->default(1)->after('receipt_remark');
            $table->string('customer_npwp')->nullable()->after('customer_address');
        });
        Schema::table('distribution_centers', function (Blueprint $table) {
            $table->string('transfer_to_virtual_account_bank_name')->nullable()->after('fo_issuance_number');
            $table->string('transfer_to_virtual_account_number')->nullable()->after('fo_issuance_number');
            $table->string('npwp')->nullable()->after('code');
        });
        Schema::table('franchises', function (Blueprint $table) {
            $table->string('transfer_to_virtual_account_bank_name')->nullable()->after('fo_issuance_number');
            $table->string('transfer_to_virtual_account_number')->nullable()->after('fo_issuance_number');
            $table->string('npwp')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('transfer_to_type');
            $table->dropColumn('customer_npwp');
        });
        Schema::table('distribution_centers', function (Blueprint $table) {
            $table->dropColumn('transfer_to_virtual_account_bank_name');
            $table->dropColumn('transfer_to_virtual_account_number');
            $table->dropColumn('npwp');
        });
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropColumn('transfer_to_virtual_account_bank_name');
            $table->dropColumn('transfer_to_virtual_account_number');
            $table->dropColumn('npwp');
        });
    }
};
