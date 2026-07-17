<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_types', function (Blueprint $table) {
            $this->catalogColumns($table);
        });

        Schema::create('container_types', function (Blueprint $table) {
            $this->catalogColumns($table);
            $table->string('color')->nullable();
        });

        Schema::create('measurement_units', function (Blueprint $table) {
            $this->catalogColumns($table);
            $table->string('symbol', 30)->nullable();
            $table->string('unit_type', 50)->nullable();
        });

        Schema::create('analytical_principles', function (Blueprint $table) {
            $this->catalogColumns($table);
        });

        Schema::create('inventory_categories', function (Blueprint $table) {
            $this->catalogColumns($table);
            $table->foreignId('parent_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
        });

        Schema::create('inventory_movement_types', function (Blueprint $table) {
            $this->catalogColumns($table);
            $table->string('direction', 20);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('affects_cost')->default(true);
            $table->index(['direction', 'status']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $this->catalogColumns($table);
            $table->boolean('requires_reference')->default(false);
        });

        Schema::create('financial_categories', function (Blueprint $table) {
            $this->catalogColumns($table);
            $table->string('type', 20);
            $table->foreignId('parent_id')->nullable()->constrained('financial_categories')->nullOnDelete();
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('inventory_movement_types');
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('analytical_principles');
        Schema::dropIfExists('measurement_units');
        Schema::dropIfExists('container_types');
        Schema::dropIfExists('sample_types');
    }

    private function catalogColumns(Blueprint $table): void
    {
        $table->id();
        $table->string('code', 50)->unique();
        $table->string('name');
        $table->text('description')->nullable();
        $table->boolean('status')->default(true);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();

        $table->index(['status', 'sort_order']);
    }
};
