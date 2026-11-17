<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_policy_id')->nullable()->constrained('rental_policies')->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->json('address')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('days');
            $table->unsignedInteger('base_fee_baisa');
            $table->unsignedInteger('extra_fee_baisa')->default(0);
            $table->unsignedInteger('deposit_baisa');
            $table->unsignedInteger('total_baisa');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->default('cod');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
