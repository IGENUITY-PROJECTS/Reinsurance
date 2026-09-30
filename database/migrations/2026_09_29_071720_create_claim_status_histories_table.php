<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_submission_id');
            $table->unsignedBigInteger('changed_by')->nullable()->index();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('remarks')->nullable();
            $table->dateTime('changed_at');
            $table->timestamps();
            $table->index(['claim_submission_id', 'changed_at'], 'claim_history_submission_changed_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_status_histories');
    }
};
