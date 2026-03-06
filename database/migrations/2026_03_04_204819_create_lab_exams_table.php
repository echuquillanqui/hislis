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
        Schema::create('lab_exams', function (Blueprint $table) {
            $table->id();
            
            // 1. EL CAMBIO CLAVE: Relación con la especialidad técnica
            $table->foreignId('specialty_lab_id')->constrained('specialty_labs')->onDelete('cascade');
            
            $table->string('name');
            $table->text('description')->nullable(); // Para notas como "Requiere 8h de ayuno"
            $table->decimal('price', 10, 2);
            $table->string('unit')->nullable(); // mg/dL, u/L, etc.
            
            // 2. Control de entrada de resultados
            $table->string('input_type'); // number, text, textarea
            $table->json('input_options')->nullable(); // Para cuando el tipo sea "select" (ej: Positivo/Negativo)
            
            // 3. Rangos de referencia
            $table->decimal('min_ref', 10, 2)->nullable();
            $table->decimal('max_ref', 10, 2)->nullable();
            
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_exams');
    }
};
