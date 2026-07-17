<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('analytical_principle_id')->nullable()->constrained('analytical_principles')->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
            $table->foreignId('sample_type_id')->nullable()->constrained('sample_types')->nullOnDelete();
            $table->foreignId('measurement_unit_id')->nullable()->constrained('measurement_units')->nullOnDelete();
            $table->string('name');
            $table->string('result_type', 50)->default('decimal');
            $table->unsignedTinyInteger('decimals')->default(2);
            $table->string('analytical_range')->nullable();
            $table->string('detection_limit')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('show_on_report')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['exam_id', 'is_default']);
            $table->index(['status', 'result_type']);
        });

        Schema::create('exam_method_reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_method_id')->constrained('exam_methods')->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('measurement_unit_id')->nullable()->constrained('measurement_units')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('sex', 10)->nullable();
            $table->unsignedInteger('min_age_days')->nullable();
            $table->unsignedInteger('max_age_days')->nullable();
            $table->boolean('pregnancy')->nullable();
            $table->string('condition')->nullable();
            $table->decimal('min_value', 14, 4)->nullable();
            $table->decimal('max_value', 14, 4)->nullable();
            $table->text('reference_text')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['exam_method_id', 'status']);
            $table->index(['sex', 'min_age_days', 'max_age_days']);
        });

        Schema::create('exam_method_critical_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_method_id')->constrained('exam_methods')->cascadeOnDelete();
            $table->decimal('low_value', 14, 4)->nullable();
            $table->decimal('high_value', 14, 4)->nullable();
            $table->text('message')->nullable();
            $table->boolean('requires_notification')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['exam_method_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_method_critical_values');
        Schema::dropIfExists('exam_method_reference_ranges');
        Schema::dropIfExists('exam_methods');
    }
};
