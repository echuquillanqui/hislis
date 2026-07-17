<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('country')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['manufacturer_id', 'status']);
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('default_turnaround_minutes')->nullable();
            $table->boolean('requires_fasting')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'name']);
        });

        Schema::create('exam_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'area_id']);
            $table->index(['area_id', 'is_primary']);
        });

        Schema::create('exam_sample_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('sample_type_id')->constrained('sample_types')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'sample_type_id']);
            $table->index(['sample_type_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sample_type');
        Schema::dropIfExists('exam_area');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('manufacturers');
    }
};
