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
        Schema::create('offer_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            // $table->integer('quantity')->default(0);

            // $table->integer('reserved_quantity')->default(0);

            $table->timestamps();

            $table->unique(['offer_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_warehouses');
    }
};
