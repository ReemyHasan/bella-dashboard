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
        Schema::table('financial_adjustments', function (Blueprint $table) {
            $table->enum('type', [
                'bonus_request',
                'deduction_request',
                'bonus_order',
                'deduction_order',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_adjustments', function (Blueprint $table) {
            //
        });
    }
};