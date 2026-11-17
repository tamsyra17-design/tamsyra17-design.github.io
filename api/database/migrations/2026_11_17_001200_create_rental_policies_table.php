<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // The base fee covers the first base_days; each later day costs extra_daily_baisa.
            $table->unsignedSmallInteger('base_days')->default(1);
            $table->unsignedInteger('base_fee_baisa');
            $table->unsignedInteger('extra_daily_baisa');
            // Optional flat price for a full 7-day rental.
            $table->unsignedInteger('weekly_fee_baisa')->nullable();
            $table->unsignedInteger('deposit_baisa')->default(0);
            $table->enum('deposit_mode', ['hold', 'charge', 'cash', 'bank_transfer'])->default('charge');
            // Whether the return day itself counts as a rental day.
            $table->boolean('return_day_inclusive')->default(false);
            // Days an item stays blocked after a return before it can go out again.
            $table->unsignedSmallInteger('buffer_days')->default(1);
            $table->unsignedSmallInteger('min_days')->default(1);
            $table->unsignedSmallInteger('max_days')->default(30);
            $table->unsignedInteger('late_fee_per_day_baisa')->default(0);
            $table->text('damage_fee_notes')->nullable();
            $table->boolean('require_id')->default(false);
            $table->boolean('require_contract')->default(false);
            // Days the shop has after a return to inspect before releasing the deposit.
            $table->unsignedSmallInteger('inspection_days')->default(2);
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_policies');
    }
};
