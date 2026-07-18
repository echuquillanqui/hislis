<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_consumable_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('estimated_quantity', 14, 4);
            $table->decimal('allowed_variance_percent', 6, 2)->default(0);
            $table->boolean('auto_consume')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['exam_id', 'product_id', 'warehouse_id'], 'exam_consumable_unique_recipe');
            $table->index(['exam_id', 'auto_consume', 'is_active'], 'exam_req_auto_active_idx');
        });

        Schema::create('lab_consumption_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_item_id')->constrained('lab_order_items')->cascadeOnDelete();
            $table->string('idempotency_key', 120)->unique();
            $table->enum('status', ['processing', 'completed', 'failed', 'reversed'])->default('processing');
            $table->unsignedInteger('reprocess_number')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['lab_order_item_id', 'status']);
        });

        Schema::create('lab_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_consumption_attempt_id')->constrained('lab_consumption_attempts')->cascadeOnDelete();
            $table->foreignId('lab_order_item_id')->constrained('lab_order_items')->cascadeOnDelete();
            $table->foreignId('exam_consumable_requirement_id')->nullable()->constrained('exam_consumable_requirements')->nullOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->decimal('estimated_quantity', 14, 4)->default(0);
            $table->decimal('actual_quantity', 14, 4);
            $table->decimal('variance_quantity', 14, 4)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_cost', 14, 4)->default(0);
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reverses_consumption_id')->nullable()->constrained('lab_consumptions')->nullOnDelete();
            $table->timestamps();

            $table->index(['lab_order_item_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_consumptions');
        Schema::dropIfExists('lab_consumption_attempts');
        Schema::dropIfExists('exam_consumable_requirements');
    }
};
