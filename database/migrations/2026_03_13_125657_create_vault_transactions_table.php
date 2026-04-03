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
        Schema::create('vault_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_vault_id')->nullable()->constrained('vaults')->nullOnDelete();
            $table->foreignId('to_vault_id')->nullable()->constrained('vaults')->nullOnDelete();

            $table->string('type');

            $table->decimal('amount', 12, 2);

            // $table->enum('direction', ['in', 'out']);

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->morphs('action_by');
            $table->nullableMorphs('reference');
            $table->nullableMorphs('balance_user');


            $table->timestamp('transaction_date')->nullable();


            $table->decimal('from_vault_balance_before', 12, 2)->nullable();
            $table->decimal('from_vault_balance_after', 12, 2)->nullable();
            $table->decimal('to_vault_balance_before', 12, 2)->nullable();
            $table->decimal('to_vault_balance_after', 12, 2)->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_transactions');
    }
};
