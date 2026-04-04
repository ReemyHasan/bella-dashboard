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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'ended'])->default('draft');
            $table->timestamp('start_at');
            $table->timestamp('end_at');

            $table->decimal('prize', 10, 2)->nullable();

            $table->morphs('created_by');

            $table->nullableMorphs('co_created_by');

            // competition type
            $table->enum('type', [
                'financial_amount',
                'orders_count',
                'product_sales',
                'offer_sales',
                'general_product_sales'
            ]);

            // generic target value (amount / count)
            $table->decimal('target_value', 12, 2)->nullable();


            $table->enum('target', [
                'all',                  // everything
                'teams',                // specific teams (includes subteams + marketers)
                'subteams',             // specific subteams (includes marketers)
                'marketers',            // specific marketers only
            ]);

            $table->timestamps();
        });

        Schema::create('competition_zone', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('competition_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->integer('target_quantity'); // number of sales
        });

        Schema::create('competition_offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();

            $table->integer('target_quantity');
        });

        Schema::create('competition_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('competition_sub_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_team_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('competition_marketers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketer_id')->constrained('app_users')->cascadeOnDelete();
        });

        Schema::create('competition_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winner_id')->constrained('app_users')->cascadeOnDelete();


            $table->decimal('achieved_value', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
