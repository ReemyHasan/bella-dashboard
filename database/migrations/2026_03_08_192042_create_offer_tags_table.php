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
        Schema::create('offer_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['offer_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_tags');
    }
};
