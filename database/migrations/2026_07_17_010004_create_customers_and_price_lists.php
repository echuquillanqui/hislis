<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->nullOnDelete();
            $table->string('document_number', 20)->nullable();
            $table->string('business_name');
            $table->string('commercial_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->boolean('allows_credit')->default(false);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->unsignedInteger('payment_due_days')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['document_type_id', 'document_number']);
            $table->index(['customer_type_id', 'status']);
            $table->index('business_name');
        });

        Schema::create('customer_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['valid_from', 'valid_to']);
        });

        Schema::create('customer_price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_price_list_id')->constrained()->cascadeOnDelete();
            $table->morphs('priceable');
            $table->decimal('price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['customer_price_list_id', 'priceable_id', 'priceable_type'], 'customer_price_item_unique');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_price_list_items');
        Schema::dropIfExists('customer_price_lists');
        Schema::dropIfExists('customers');
    }
};
