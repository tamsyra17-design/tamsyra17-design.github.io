<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_unit_id')->nullable()->constrained('rental_units')->nullOnDelete();
            $table->json('name');
            $table->string('sku')->nullable();
            $table->unsignedInteger('unit_price_baisa');
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedInteger('line_total_baisa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
