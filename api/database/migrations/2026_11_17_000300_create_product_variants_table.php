<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64)->unique();
            $table->json('name');
            // Variant price in baisa. Null means "use the product price".
            $table->unsignedInteger('price_baisa')->nullable();
            // Units available for sale (rental inventory is tracked separately).
            $table->unsignedInteger('sale_stock')->default(0);
            // Free-form options such as {"size": "M", "colour": "red"}.
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
