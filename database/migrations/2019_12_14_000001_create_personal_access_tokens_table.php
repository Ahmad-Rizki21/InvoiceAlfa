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
        Schema::create('user_access_tokens', function (Blueprint $table) {
            $this->generateTableDefinition($table);
        });

        Schema::create('distribution_center_access_tokens', function (Blueprint $table) {
            $this->generateTableDefinition($table);
        });

        Schema::create('franchise_access_tokens', function (Blueprint $table) {
            $this->generateTableDefinition($table);
        });
    }

    protected function generateTableDefinition(Blueprint $table)
    {
        $table->ulid('id')->primary();
        $table->unsignedBigInteger('user_id');
        $table->integer('client_code');
        $table->string('name')->nullable();
        $table->string('token', 64)->unique();
        $table->text('abilities')->nullable();
        $table->text('user_agent')->nullable();
        $table->ipAddress('ip_address')->nullable();
        $table->unsignedTinyInteger('revoked')->default(0);
        $table->timestamp('last_used_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_access_tokens');
        Schema::dropIfExists('distribution_center_access_tokens');
        Schema::dropIfExists('franchise_access_tokens');
    }
};
