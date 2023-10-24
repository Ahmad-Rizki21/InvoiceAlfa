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
        Schema::create('invoice_services', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description', 368)->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('unit_price', 13, 2)->nullable();
            $table->decimal('sub_total', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_services');
    }
};
