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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);



            $table->decimal('marketer_percentage', 5, 2)->default(0);
            $table->decimal('team_leader_percentage', 5, 2)->default(0);
            $table->decimal('manager_percentage', 5, 2)->default(0);
            $table->decimal('direct_manager_percentage', 5, 2)->default(0);
            // $table->decimal('delivery_man_percentage', 5, 2)->default(0);
            // $table->decimal('warehouse_man_percentage', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
