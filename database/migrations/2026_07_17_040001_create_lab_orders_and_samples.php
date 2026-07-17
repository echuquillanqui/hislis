<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_order_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('next_number')->default(1);
            $table->timestamps();

            $table->unique(['branch_id', 'year']);
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('requesting_physician_id')->nullable()->constrained('requesting_physicians')->nullOnDelete();
            $table->foreignId('tariff_id')->nullable()->constrained('tariffs')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->dateTime('ordered_at');
            $table->enum('status', ['draft', 'registered', 'sample_pending', 'sample_collected', 'processing', 'completed', 'cancelled'])->default('registered');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('clinical_notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'ordered_at']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->morphs('orderable');
            $table->foreignId('parent_profile_id')->nullable()->constrained('exam_profiles')->cascadeOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['pending', 'sample_pending', 'sample_collected', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['lab_order_id', 'status']);
            $table->index(['exam_id', 'parent_profile_id']);
        });

        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignId('sample_type_id')->constrained('sample_types')->restrictOnDelete();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('barcode', 60)->unique();
            $table->enum('status', ['pending', 'collected', 'received', 'rejected', 'cancelled'])->default('pending');
            $table->dateTime('collected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['lab_order_id', 'status']);
        });

        Schema::create('lab_order_item_lab_sample', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_item_id')->constrained('lab_order_items')->cascadeOnDelete();
            $table->foreignId('lab_sample_id')->constrained('lab_samples')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lab_order_item_id', 'lab_sample_id'], 'lab_item_sample_unique');
        });

        Schema::create('lab_sample_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_sample_id')->constrained('lab_samples')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['lab_sample_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_sample_events');
        Schema::dropIfExists('lab_order_item_lab_sample');
        Schema::dropIfExists('lab_samples');
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('lab_order_sequences');
    }
};
