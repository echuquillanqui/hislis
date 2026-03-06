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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('status')->default(true); // Activo/Inactivo
            $table->boolean('is_medical')->default(true); // Activo/Inactivo
            // Permite que un área sea sub-área de otra (ej: Ginecología > Ecografía)
            $table->foreignId('parent_id')->nullable()->constrained('areas')->onDelete('cascade');
            // El formato que heredará cualquier servicio de esta área si no tiene uno propio
            $table->foreignId('template_id')->nullable()->constrained('templates');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
