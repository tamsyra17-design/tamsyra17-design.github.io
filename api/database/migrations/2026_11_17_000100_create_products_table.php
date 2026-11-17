<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Nullable so deleting a category orphans its products instead of
            // blocking the delete or cascading it across the catalogue.
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 64)->unique();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            // Sale price in baisa (1 OMR = 1000 baisa). Null means "not for sale".
            $table->unsignedInteger('price_baisa')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_rentable')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('weight_grams')->nullable();
            // Lower-cased sku + ar/en name + short description, rebuilt on save.
            $table->text('search_text')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'is_rentable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
