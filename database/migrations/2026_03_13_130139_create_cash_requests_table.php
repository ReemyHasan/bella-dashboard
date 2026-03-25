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
        Schema::create('cash_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_vault_id')->constrained('vaults')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->cascadeOnDelete();

            $table->decimal('requested_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->cascadeOnDelete();
            $table->text('address_details')->nullable();

            $table->text('cash_request_reason')->nullable();
            $table->text('notes')->nullable();


            $table->enum('status', [
                "pending",
                "approved",
                "rejected",
                "in_transit",
                "delivered",
                "completed",
                "cancelled",
                "waiting_delivery_approve",
                "not_delivered",
            ])->default('pending');

            $table->morphs('requested_by');
            $table->morphs('requested_for');
            $table->foreignId('delivered_by')->nullable()->constrained('app_users')->cascadeOnDelete();

            $table->decimal('delivery_cost', 12, 2)->nullable();
            $table->decimal('additional_delivery_cost', 12, 2)->nullable();

            $table->foreignId('currency_id')->nullable()->constrained('currencies')->cascadeOnDelete();
            $table->decimal('current_exchange_value', 12, 2)->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('dash_users')->cascadeOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_requests');
    }
};
