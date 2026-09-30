<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_feedback', function (Blueprint $table) {
            $table->id();
            // Each message belongs to exactly one submission (validated by the model).
            $table->unsignedBigInteger('claim_submission_id')->nullable()->index();
            $table->unsignedBigInteger('premium_adjustment_submission_id')->nullable()->index('feedback_premium_adjustment_index');
            $table->unsignedBigInteger('author_id')->index();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_feedback');
    }
};
