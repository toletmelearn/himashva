<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->enum('vote', ['up', 'down']);
            $table->timestamps();

            $table->unique(['review_id', 'user_id']);
            $table->unique(['review_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_votes');
    }
};
