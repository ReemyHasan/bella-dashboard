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
        Schema::create('sub_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');

            $table->boolean('active')->default(true);
            $table->boolean('is_direct')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_teams');
    }
};
