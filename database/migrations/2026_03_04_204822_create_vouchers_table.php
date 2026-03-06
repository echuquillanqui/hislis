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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Cajero
            $table->string('type'); // 01 Factura, 03 Boleta, 00 Ticket
            $table->string('series');
            $table->string('number');
            $table->decimal('total', 12, 2);
            $table->enum('status', ['paid', 'pending', 'canceled'])->default('paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
