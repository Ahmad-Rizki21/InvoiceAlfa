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
        Schema::create('distribution_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('username', 30)->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('landline_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->string('password')->nullable();
            $table->string('locale', 5)->default('id');
            $table->dateTime('approval_date')->nullable();
            $table->dateTime('fo_approval_date')->nullable();
            $table->string('offering_letter_reference_number')->nullable();
            $table->string('fo_offering_letter_reference_number')->nullable();
            $table->string('issuance_number')->nullable();
            $table->string('fo_issuance_number')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_centers');
    }
};
