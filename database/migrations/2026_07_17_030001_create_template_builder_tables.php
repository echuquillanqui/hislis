<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('publication_status')->default('draft')->after('schema');
            $table->unsignedInteger('published_version_number')->nullable()->after('publication_status');
            $table->timestamp('published_at')->nullable()->after('published_version_number');
            $table->foreignId('published_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status')->default('draft');
            $table->json('schema_snapshot')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['template_id', 'version_number']);
        });

        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_version_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_section_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('slug');
            $table->string('type');
            $table->unsignedTinyInteger('column')->default(12);
            $table->boolean('required')->default(false);
            $table->string('unit')->nullable();
            $table->string('placeholder')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('template_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_field_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('template_conditional_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_field_id')->constrained()->cascadeOnDelete();
            $table->string('source_field_slug');
            $table->string('operator')->default('equals');
            $table->string('comparison_value')->nullable();
            $table->string('action')->default('show');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_conditional_rules');
        Schema::dropIfExists('template_field_options');
        Schema::dropIfExists('template_fields');
        Schema::dropIfExists('template_sections');
        Schema::dropIfExists('template_versions');
        Schema::table('templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_by');
            $table->dropColumn(['code', 'publication_status', 'published_version_number', 'published_at']);
        });
    }
};
