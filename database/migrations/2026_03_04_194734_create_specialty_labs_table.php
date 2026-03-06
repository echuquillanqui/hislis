<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('specialty_labs', function (Blueprint $table) {
            $table->id();
            // Relación con tu tabla 'areas' (El área Laboratorio)
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('name'); // Bioquímica, Hematología, etc.
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialty_labs');
    }
};
