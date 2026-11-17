<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Catalogue links are nulled if the product is deleted; the snapshot
            // columns below keep the line readable afterwards.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            // {"product": {"ar", "en"}, "variant": {"ar", "en"}} at time of order.
            $table->json('name');
            $table->string('sku', 64);
            $table->unsignedInteger('unit_price_baisa');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('line_total_baisa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
