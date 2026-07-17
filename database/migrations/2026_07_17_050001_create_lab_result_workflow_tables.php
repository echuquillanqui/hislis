<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('requires_fasting')->constrained('templates')->nullOnDelete();
        });

        Schema::create('lab_result_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_item_id')->constrained('lab_order_items')->cascadeOnDelete();
            $table->foreignId('template_version_id')->nullable()->constrained('template_versions')->nullOnDelete();
            $table->foreignId('exam_method_id')->nullable()->constrained('exam_methods')->nullOnDelete();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technically_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('professionally_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('content')->nullable();
            $table->unsignedInteger('report_version')->default(1);
            $table->enum('status', ['draft', 'entered', 'technical_validated', 'approved', 'corrected'])->default('draft');
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('technically_validated_at')->nullable();
            $table->timestamp('professionally_approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->unique('lab_order_item_id');
            $table->index(['status', 'report_version']);
        });

        Schema::create('lab_result_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_result_record_id')->constrained('lab_result_records')->cascadeOnDelete();
            $table->foreignId('template_field_id')->nullable()->constrained('template_fields')->nullOnDelete();
            $table->foreignId('exam_method_reference_range_id')->nullable()->constrained('exam_method_reference_ranges')->nullOnDelete();
            $table->string('field_slug');
            $table->string('field_label');
            $table->string('field_type', 50);
            $table->string('unit')->nullable();
            $table->text('value')->nullable();
            $table->decimal('numeric_value', 14, 4)->nullable();
            $table->decimal('reference_min', 14, 4)->nullable();
            $table->decimal('reference_max', 14, 4)->nullable();
            $table->text('reference_text')->nullable();
            $table->enum('flag', ['normal', 'low', 'high', 'critical_low', 'critical_high'])->default('normal');
            $table->boolean('is_abnormal')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->timestamps();

            $table->unique(['lab_result_record_id', 'field_slug'], 'lab_result_value_slug_unique');
            $table->index(['flag', 'is_critical']);
        });

        Schema::create('lab_result_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_result_record_id')->constrained('lab_result_records')->cascadeOnDelete();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('from_report_version');
            $table->unsignedInteger('to_report_version');
            $table->json('previous_content')->nullable();
            $table->json('new_content')->nullable();
            $table->text('reason');
            $table->timestamp('corrected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_result_corrections');
        Schema::dropIfExists('lab_result_values');
        Schema::dropIfExists('lab_result_records');
        Schema::table('exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_id');
        });
    }
};
