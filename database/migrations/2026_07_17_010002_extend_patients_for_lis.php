<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->after('id');
            $table->string('document_number', 20)->nullable()->after('document_type_id');
            $table->string('email')->nullable()->after('phone');
            $table->string('address')->nullable()->after('email');
            $table->string('clinical_history_number')->nullable()->after('address');
            $table->string('origin')->nullable()->after('clinical_history_number');
            $table->text('observations')->nullable()->after('origin');
            $table->boolean('status')->default(true)->after('observations');

            $table->index('document_type_id');
            $table->unique(['document_type_id', 'document_number']);
            $table->foreign('document_type_id')->references('id')->on('document_types')->nullOnDelete();
            $table->index(['last_name', 'first_name']);
            $table->index('clinical_history_number');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropUnique(['document_type_id', 'document_number']);
            $table->dropIndex(['last_name', 'first_name']);
            $table->dropIndex(['clinical_history_number']);
            $table->dropIndex(['document_type_id']);
            $table->dropColumn([
                'document_type_id',
                'document_number',
                'email',
                'address',
                'clinical_history_number',
                'origin',
                'observations',
                'status',
            ]);
        });
    }
};
