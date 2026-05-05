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
        Schema::create('warehouse_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')
                ->constrained('app_users')
                ->cascadeOnDelete();

            // reviewed (warehouse man)
            $table->foreignId('reviewed_user_id')
                ->constrained('app_users')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rating'); 
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(['reviewer_id', 'reviewed_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_reviews');
    }
};
