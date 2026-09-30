<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_adjustment_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submission_reference', 26)->unique();
            $table->string('adjustment_reference', 50);
            $table->string('company_code', 50)->index();
            $table->string('CoverNo', 50)->nullable()->index();
            $table->unsignedBigInteger('submitted_by')->nullable()->index();
            $table->string('portal_status', 40)->default('draft');
            $table->text('details')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->unique(['company_code', 'adjustment_reference'], 'premium_adjustment_company_reference_unique');
            $table->index(['company_code', 'portal_status'], 'premium_adjustment_company_status_index');
            // Source-mirror fields will be designed after the RBS schema is supplied.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_adjustment_submissions');
    }
};
