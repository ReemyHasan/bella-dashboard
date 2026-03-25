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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('description');

            $table->dateTime('appears_from')->nullable();
            $table->dateTime('appears_to')->nullable();

            $table->enum('assignment_type', ['all', 'specific']);
            $table->enum('target_type', ['team', 'sub_team', 'marketer']);

            // $table->boolean('is_assigned_to_all_teams')->default(false);
            // $table->boolean('is_assigned_to_specific_teams')->default(false);

            // $table->boolean('is_assigned_to_all_sub_teams')->default(false);
            // $table->boolean('is_assigned_to_specific_sub_teams')->default(false);

            // $table->boolean('is_assigned_to_all_marketers')->default(false);
            // $table->boolean('is_assigned_to_specific_marketers')->default(false);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
