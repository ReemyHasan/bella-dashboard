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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->foreignId('region_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('delivery_man_id')
                ->nullable()
                ->constrained('app_users')
                ->nullOnDelete();

            $table->foreignId('alter_delivery_man_id')
                ->nullable()
                ->constrained('app_users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
