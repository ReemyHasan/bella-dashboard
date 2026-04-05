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
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('customer_id')->constrained('customers');

            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('sub_team_id')->nullable()->constrained('sub_teams')->nullOnDelete();

            $table->text('address_details')->nullable();

            $table->string('customer_mobile');

            $table->foreignId('app_user_id')->nullable()->constrained('app_users'); // marketer
            $table->decimal('marketer_percentage', 8, 2)->default(0);

            $table->foreignId('warehouse_man_id')->nullable()->constrained('app_users');
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('delivery_additional_cost', 10, 2)->default(0);

            $table->foreignId('teamleader_id')->nullable()->constrained('app_users');
            $table->decimal('teamleader_percentage', 8, 2)->default(0);

            $table->foreignId('manager_id')->nullable()->constrained('app_users');
            $table->decimal('manager_percentage', 8, 2)->default(0);


            $table->decimal('marketer_amount', 8, 2)->default(0);
            $table->decimal('teamleader_amount', 8, 2)->default(0);
            $table->decimal('manager_amount', 8, 2)->default(0);
            $table->decimal('warehouse_man_amount', 8, 2)->default(0);



            $table->decimal('total_base_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->decimal('additional_tips', 10, 2)->default(0);

            $table->foreignId('warehouse_id')->constrained();

            $table->enum('order_status', [
                'new',
                'delivering',
                'waiting',
                'cancelled',
                'completed',
                'refund'
            ])->default('new');


            // $table->decimal('deduction_amount', 10, 2)->default(0);
            // $table->enum('deduction_type', ['fixed', 'percentage'])->nullable();

            $table->decimal('current_exchange_rate', 10, 4)->default(1);

            $table->foreignId('currency_id')->constrained();

            $table->string('cancellation_reason')->nullable();
            $table->string('waiting_reason')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('waiting_until')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->index('customer_id');
            $table->index('warehouse_id');
            $table->index('order_status');



            $table->morphs('created_by');
            $table->foreignId('reviewed_by')->nullable()->constrained('dash_users')->cascadeOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->boolean('is_financial_processed')->default(false);
            $table->boolean('is_stock_reserved')->default(false);


            $table->enum('adjustment_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('adjustment_value', 10, 2)->nullable();
            $table->enum('adjustment_operation', ['increase', 'decrease'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};
