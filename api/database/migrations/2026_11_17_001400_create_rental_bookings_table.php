<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_unit_id')->constrained()->cascadeOnDelete();
            // The unit goes out on start_date and comes back on end_date.
            $table->date('start_date');
            $table->date('end_date');
            // Null for admin blocks. The rentals table arrives in a later task,
            // which should add the foreign key constraint.
            $table->unsignedBigInteger('rental_id')->nullable()->index();
            $table->timestamps();

            $table->index(['rental_unit_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
    }
};
