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
        Schema::create('vault_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_vault_id')->nullable()->constrained('vaults')->nullOnDelete();
            $table->foreignId('to_vault_id')->nullable()->constrained('vaults')->nullOnDelete();


            $table->decimal('amount', 12, 2);


            $table->morphs('created_by');

            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
            ])->default('pending');

            $table->timestamp('transferred_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_transfers');
    }
};
