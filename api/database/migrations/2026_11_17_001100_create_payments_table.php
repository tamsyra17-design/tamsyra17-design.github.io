<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // Orders now; rentals and deposits later.
            $table->morphs('payable');
            // The adapter that handles it: "cod", "bank_transfer" or a gateway name.
            $table->string('gateway', 32);
            $table->string('method', 32);
            $table->unsignedInteger('amount_baisa');
            $table->string('status', 32)->default('pending')->index();
            // Gateway / bank reference; webhooks are idempotent on it.
            $table->string('transaction_ref')->nullable()->unique();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
