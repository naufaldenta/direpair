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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diagnosis_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->string('status', 24)->index();
            $table->char('currency', 3)->default('IDR');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total');
            $table->boolean('deposit_required')->default(false);
            $table->unsignedBigInteger('deposit_amount')->default(0);
            $table->text('customer_notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_ip_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['service_request_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
