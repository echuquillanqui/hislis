<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('period_month');
            $table->string('status', 20)->default('draft');
            $table->timestamp('counted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['warehouse_id', 'period_month']);
            $table->index(['period_month', 'status']);
        });

        Schema::create('monthly_inventory_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_inventory_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->restrictOnDelete();
            $table->decimal('snapshot_quantity', 14, 4)->default(0);
            $table->decimal('counted_quantity', 14, 4)->nullable();
            $table->decimal('difference_quantity', 14, 4)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('valuation_amount', 14, 4)->default(0);
            $table->boolean('adjustment_applied')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['monthly_inventory_count_id', 'product_id', 'inventory_lot_id'], 'monthly_count_line_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_inventory_count_lines');
        Schema::dropIfExists('monthly_inventory_counts');
    }
};
