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
        Schema::create('triages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Enfermera/o
            
            // Signos Vitales
            $table->decimal('temp', 4, 1); // Temperatura
            $table->integer('hr'); // Heart Rate (Frecuencia Cardiaca)
            $table->integer('rr'); // Respiratory Rate (Frecuencia Respiratoria)
            $table->string('bp');  // Blood Pressure (Presión Arterial ej: 120/80)
            $table->decimal('weight', 5, 2); // Kg
            $table->decimal('height', 4, 2); // Metros
            $table->decimal('bmi', 5, 2);    // IMC (Calculado)
            $table->integer('spo2')->nullable(); // Saturación de Oxígeno
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triages');
    }
};
