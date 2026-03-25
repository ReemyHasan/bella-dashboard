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
        Schema::create('message_assignees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('message_id')
                ->constrained('messages')
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sub_team_id')
                ->nullable()
                ->constrained('sub_teams')
                ->cascadeOnDelete();

            $table->foreignId('marketer_id')
                ->nullable()
                ->constrained('app_users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_assignees');
    }
};
