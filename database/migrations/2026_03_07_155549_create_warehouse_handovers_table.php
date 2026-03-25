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
        Schema::create('warehouse_handovers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requester_warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            $table->foreignId('provider_warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            $table->enum('status', [
                "pending",
                "approved",
                "rejected",
                "in_transit",
                "delivered",
                "completed",
                "cancelled"
            ])->default('pending');


            $table->text('notes')->nullable();

            $table->dateTime('approved_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->foreignId('requested_by')->nullable()->constrained("dash_users")->nullOnDelete();
            $table->foreignId('responded_by')->nullable()->constrained("dash_users")->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_handovers');
    }
};

 