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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_center_id')->nullable();
            $table->unsignedBigInteger('franchise_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('fo_approval_date')->nullable();
            $table->string('offering_letter_reference_number')->nullable();
            $table->string('fo_offering_letter_reference_number')->nullable();
            $table->string('issuance_number')->nullable();
            $table->string('fo_issuance_number')->nullable();
            $table->unsignedTinyInteger('ppn_percentage')->nullable();
            $table->decimal('ppn_total', 14, 2)->nullable();
            $table->unsignedInteger('stamp_duty')->nullable();
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->text('note')->nullable();
            $table->text('receipt_remark')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamp('unpaid_updated_at')->nullable();
            $table->timestamp('pending_review_updated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
