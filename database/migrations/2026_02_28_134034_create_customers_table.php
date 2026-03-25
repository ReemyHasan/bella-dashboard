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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('password');
            $table->string('mobile')->unique();
            $table->string('user_name')->unique();
            $table->string('profile_link')->nullable();

            $table->boolean('is_blocked')->default(false);
            $table->dateTime('blocked_date')->nullable();
            $table->text('blocked_reason')->nullable();

            // Morphs
            $table->morphs('created_by');   // 
            $table->nullableMorphs('blocked_by');
            $table->nullableMorphs('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
