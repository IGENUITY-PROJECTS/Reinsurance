<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 26)->unique();
            $table->string('subject', 200);
            $table->unsignedBigInteger('created_by')->index();
            $table->string('company_code', 50)->nullable()->index();
            $table->timestamps();
        });
        Schema::create('feedback_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_conversation_id')->constrained('feedback_conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('author_id')->index();
            $table->text('message');
            $table->timestamps();
            $table->index(['feedback_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_messages');
        Schema::dropIfExists('feedback_conversations');
    }
};
