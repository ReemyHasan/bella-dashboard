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
        Schema::create('address_app_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('app_user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_main')->default(false);

            $table->timestamps();

            $table->unique(['address_id', 'app_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_app_user');
    }
};
