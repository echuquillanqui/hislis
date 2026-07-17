<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requesting_physicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->nullOnDelete();
            $table->foreignId('physician_specialty_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_number', 20)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('license_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['document_type_id', 'document_number']);
            $table->index(['last_name', 'first_name']);
            $table->index('license_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requesting_physicians');
    }
};
