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
        Schema::create('financial_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_vault_id')->nullable()->constrained('vaults')->nullOnDelete();
            $table->foreignId('to_vault_id')->nullable()->constrained('vaults')->nullOnDelete();

            $table->enum('type', ['bonus', 'deduction']);

            $table->decimal('amount', 12, 2);

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->morphs('requested_by');
            $table->morphs('requested_for');

            $table->foreignId('reviewed_by')->nullable()->constrained('dash_users')->cascadeOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->text('review_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_adjustments');
    }
};
