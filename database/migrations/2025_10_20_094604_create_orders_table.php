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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tran_id', 40)->unique();     // reference used with PayWay
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD'); // USD|KHR
            $table->string('status', 20)->default('pending'); // pending|approved|failed|cancelled
            // optional customer info
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('phone', 30)->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();              // your custom data
            $table->json('gateway_response')->nullable();  // PayWay result/raw payload

            $table->timestamps();
            $table->index(['status', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
