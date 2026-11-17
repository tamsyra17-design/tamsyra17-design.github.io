<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            // Guest checkout is allowed, so the account link is optional.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            // Guests look an order up by number + phone.
            $table->string('phone', 32);
            $table->string('email')->nullable();
            // Snapshot of the delivery address as entered at checkout.
            $table->json('address');
            // Money in baisa (1 OMR = 1000 baisa).
            $table->unsignedInteger('subtotal_baisa');
            $table->unsignedInteger('shipping_baisa');
            $table->unsignedInteger('total_baisa');
            $table->string('status', 32)->default('pending')->index();
            $table->string('payment_status', 32)->default('pending')->index();
            $table->string('payment_method', 32);
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
