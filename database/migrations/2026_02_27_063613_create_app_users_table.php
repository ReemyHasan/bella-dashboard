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
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('user_name')->unique();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('subteam_id')
                ->nullable()
                ->constrained('sub_teams')
                ->nullOnDelete();

            $table->boolean('is_delivery_man')->default(false);
            $table->boolean('is_warehouse_man')->default(false);

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();


            $table->string('password');

            $table->date('birth_date')->nullable();
            $table->date('join_date')->nullable();

            $table->string('mobile')->nullable();
            $table->string('profile_link')->nullable();

            $table->enum('status', ['ACTIVE', 'INACTIVE', 'BANNED'])->default('ACTIVE');

            $table->foreignId('created_by_app_user_id')
                ->nullable()
                ->constrained('app_users')
                ->nullOnDelete();

            $table->foreignId('created_by_dash_user_id')
                ->nullable()
                ->constrained('dash_users')
                ->nullOnDelete();

            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
