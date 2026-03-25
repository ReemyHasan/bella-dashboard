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
        Schema::create('app_user_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_user_id')->nullable()->constrained("app_users")->nullOnDelete();
            $table->foreignId('user_request_type_id')->nullable()->constrained("user_request_types")->nullOnDelete();
            $table->text('content');
            $table->dateTime('read_at')->nullable();
            $table->dateTime('handled_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_user_requests');
    }
};
