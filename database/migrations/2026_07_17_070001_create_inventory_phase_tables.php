<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('inventory_category_id')->nullable()->after('category')->constrained('inventory_categories')->nullOnDelete();
            $table->foreignId('measurement_unit_id')->nullable()->after('inventory_category_id')->constrained('measurement_units')->nullOnDelete();
            $table->boolean('is_lot_controlled')->default(true)->after('measurement_unit_id');
            $table->boolean('requires_fefo')->default(true)->after('is_lot_controlled');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('name')->constrained('areas')->nullOnDelete();
            $table->boolean('status')->default(true)->after('area_id');
        });

        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('lot_number');
            $table->date('expiry_date')->nullable();
            $table->date('received_at')->nullable();
            $table->string('status', 30)->default('available');
            $table->timestamps();

            $table->unique(['product_id', 'lot_number']);
            $table->index(['product_id', 'status', 'expiry_date']);
        });

        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->cascadeOnDelete();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id', 'inventory_lot_id'], 'inventory_balances_unique_stock');
            $table->index(['product_id', 'warehouse_id', 'quantity']);
        });

        Schema::create('inventory_kardex_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type', 30);
            $table->decimal('quantity_in', 14, 4)->default(0);
            $table->decimal('quantity_out', 14, 4)->default(0);
            $table->decimal('balance_after', 14, 4);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'occurred_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_kardex_entries');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('inventory_lots');
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
            $table->dropColumn('status');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('measurement_unit_id');
            $table->dropConstrainedForeignId('inventory_category_id');
            $table->dropColumn(['is_lot_controlled', 'requires_fefo']);
        });
    }
};
