<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('type', 20);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['type', 'status']);
        });

        Schema::create('finance_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status', 20)->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reopened_at')->nullable();
            $table->text('closing_notes')->nullable();
            $table->text('reopening_reason')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'starts_at', 'ends_at']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('finance_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_period_id')->constrained('finance_periods')->restrictOnDelete();
            $table->foreignId('finance_category_id')->constrained('finance_categories')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('lab_order_id')->nullable()->constrained('lab_orders')->nullOnDelete();
            $table->foreignId('cash_movement_id')->nullable()->constrained('cash_movements')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20);
            $table->string('status', 20)->default('confirmed');
            $table->decimal('amount', 12, 2);
            $table->date('occurred_on');
            $table->string('reference', 120)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['finance_period_id', 'type', 'status']);
            $table->index(['branch_id', 'occurred_on']);
        });

        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_period_id')->constrained('finance_periods')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            $table->date('issued_on');
            $table->date('due_on')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
            $table->unique('lab_order_id');
            $table->index(['branch_id', 'status', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('finance_movements');
        Schema::dropIfExists('finance_periods');
        Schema::dropIfExists('finance_categories');
    }
};
