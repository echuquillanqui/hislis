<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('microorganisms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('gram_stain', 30)->nullable();
            $table->string('group')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('antibiotics', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('family')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('microbiology_cultures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_result_record_id')->constrained('lab_result_records')->cascadeOnDelete();
            $table->foreignId('lab_sample_id')->nullable()->constrained('lab_samples')->nullOnDelete();
            $table->string('culture_type');
            $table->enum('growth_status', ['pending', 'no_growth', 'positive'])->default('pending');
            $table->string('colony_count')->nullable();
            $table->text('direct_exam')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('incubated_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();

            $table->index(['growth_status', 'culture_type']);
        });

        Schema::create('microbiology_isolates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('microbiology_culture_id')->constrained('microbiology_cultures')->cascadeOnDelete();
            $table->foreignId('microorganism_id')->constrained('microorganisms')->restrictOnDelete();
            $table->unsignedSmallInteger('isolate_number');
            $table->string('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['microbiology_culture_id', 'isolate_number'], 'micro_isolate_number_unique');
        });

        Schema::create('antibiotic_susceptibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('microbiology_isolate_id')->constrained('microbiology_isolates')->cascadeOnDelete();
            $table->foreignId('antibiotic_id')->constrained('antibiotics')->restrictOnDelete();
            $table->enum('interpretation', ['S', 'I', 'R']);
            $table->string('method', 50)->default('disk_diffusion');
            $table->decimal('mic', 8, 3)->nullable();
            $table->decimal('disk_diffusion_mm', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['microbiology_isolate_id', 'antibiotic_id'], 'micro_susceptibility_unique');
            $table->index('interpretation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antibiotic_susceptibilities');
        Schema::dropIfExists('microbiology_isolates');
        Schema::dropIfExists('microbiology_cultures');
        Schema::dropIfExists('antibiotics');
        Schema::dropIfExists('microorganisms');
    }
};
