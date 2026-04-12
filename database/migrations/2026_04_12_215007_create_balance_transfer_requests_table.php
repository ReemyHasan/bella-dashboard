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
        Schema::create('balance_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('app_users')->cascadeOnDelete();

            // Receiver
            $table->foreignId('to_user_id')->constrained('app_users')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('status')->default('pending'); // pending | approved | rejected

            $table->text('notes')->nullable();        // user note
            $table->text('review_notes')->nullable(); // admin note

            $table->foreignId('reviewed_by')->nullable()->constrained('dash_users')->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_transfer_requests');
    }
};
