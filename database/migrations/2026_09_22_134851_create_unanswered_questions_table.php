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
        Schema::create('unanswered_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            $table->string('normalized_question', 500)->unique();
            $table->unsignedInteger('frequency')->default(1);
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'resolved', 'ignored'])->default('pending');
            $table->foreignId('resolved_by_response_id')->nullable()->constrained('chatbot_responses')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unanswered_questions');
    }
};
