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
        Schema::table('product_zone_prices', function (Blueprint $table) {
            $table->decimal('price_after_adjustment', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_zone_prices', function (Blueprint $table) {
            //
        });
    }
};
