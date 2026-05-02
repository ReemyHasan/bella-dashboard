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
        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')
                ->constrained()
                ->cascadeOnDelete();

            // $table->foreignId('user_id')
            //     ->constrained('app_users')
            //     ->cascadeOnDelete();

            $table->morphs('participant');
            $table->decimal('score', 12, 2)->default(0);
            $table->decimal('progress', 5, 2)->default(0);

            $table->boolean('is_winner')->default(false);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
    }
};
