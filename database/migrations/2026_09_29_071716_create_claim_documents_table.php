<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_submission_id')->index();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->string('document_type', 50)->nullable();
            $table->string('original_name', 255);
            // local points to storage/app/private in this project.
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
};
