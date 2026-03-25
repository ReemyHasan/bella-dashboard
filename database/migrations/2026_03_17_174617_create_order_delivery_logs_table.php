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
        Schema::create('order_delivery_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_man_id')
                ->nullable()
                ->constrained('app_users');

            $table->enum('status', [
                'assigned',
                'picked_up',
                'on_the_way',
                'delivered',
                'failed'
            ]);

            $table->string('failure_reason')->nullable();

            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_logs');
    }
};
