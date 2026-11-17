<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            // Weight band, inclusive at both ends. A null upper bound means
            // "and heavier".
            $table->unsignedInteger('min_weight_grams')->default(0);
            $table->unsignedInteger('max_weight_grams')->nullable();
            $table->unsignedInteger('rate_baisa');
            // Shipping in this band is free once the subtotal reaches this amount.
            $table->unsignedInteger('free_over_baisa')->nullable();
            $table->timestamps();

            $table->index(['shipping_zone_id', 'min_weight_grams']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
