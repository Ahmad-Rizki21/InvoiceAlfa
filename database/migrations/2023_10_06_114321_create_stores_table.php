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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_center_id');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('landline_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->dateTime('fo_approval_date')->nullable();
            $table->string('offering_letter_reference_number')->nullable();
            $table->string('fo_offering_letter_reference_number')->nullable();
            $table->string('issuance_number')->nullable();
            $table->string('fo_issuance_number')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
