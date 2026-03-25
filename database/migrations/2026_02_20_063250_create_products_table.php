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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('main_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('size')->nullable();

            $table->text('description')->nullable();
            $table->text('how_to_use')->nullable();
            $table->text('precautions')->nullable();
            $table->string('country_of_origin')->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
