<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 32);
            $table->string('external_order_id')->unique();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('status', 24)->index();
            $table->char('currency', 3)->default('IDR');
            $table->unsignedBigInteger('amount');
            $table->string('gateway_status', 64)->nullable();
            $table->string('fraud_status', 64)->nullable();
            $table->string('checkout_token')->nullable();
            $table->text('checkout_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
