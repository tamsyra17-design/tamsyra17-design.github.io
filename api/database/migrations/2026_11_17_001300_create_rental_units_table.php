<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Shop-facing identifier for the physical piece, e.g. a tag number.
            $table->string('code');
            $table->enum('status', ['available', 'maintenance', 'retired'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_units');
    }
};
