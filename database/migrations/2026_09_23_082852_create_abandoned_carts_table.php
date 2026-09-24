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
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id');
            $table->string('email')->nullable();
            $table->json('cart_data');
            $table->decimal('total', 10, 2);
            $table->enum('status', ['active', 'reminded', 'recovered', 'expired'])->default('active');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->unsignedInteger('reminder_count')->default(0);
            $table->foreignId('recovered_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['status', 'email', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
