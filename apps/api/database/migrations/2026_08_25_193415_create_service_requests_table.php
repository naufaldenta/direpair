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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('public_number', 24)->unique();
            $table->string('public_token_hash', 64)->unique();
            $table->string('service_slug', 120)->index();
            $table->string('device_category', 120);
            $table->string('brand', 120)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('serial_number', 120)->nullable();
            $table->text('symptom');
            $table->string('preferred_service_method', 32);
            $table->json('service_address')->nullable();
            $table->string('urgency', 24)->default('normal');
            $table->string('status', 40)->index();
            $table->string('payment_status', 32)->default('not_required')->index();
            $table->string('source', 40)->default('website');
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamp('submitted_at');
            $table->timestamp('warranty_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
