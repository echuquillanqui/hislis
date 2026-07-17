<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'name']);
        });

        Schema::create('exam_profile_exam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_profile_id')->constrained('exam_profiles')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['exam_profile_id', 'exam_id']);
        });

        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('currency', 5)->default('PEN');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['valid_from', 'valid_to']);
        });

        Schema::create('tariff_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tariff_id')->constrained('tariffs')->cascadeOnDelete();
            $table->morphs('tariffable');
            $table->decimal('price', 12, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['tariff_id', 'tariffable_id', 'tariffable_type'], 'tariff_item_unique');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_items');
        Schema::dropIfExists('tariffs');
        Schema::dropIfExists('exam_profile_exam');
        Schema::dropIfExists('exam_profiles');
    }
};
