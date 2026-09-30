<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submission_reference', 26)->unique();
            $table->string('company_code', 50)->index();
            $table->unsignedBigInteger('submitted_by')->nullable()->index();
            $table->string('portal_status', 40)->default('draft');
            $table->dateTime('submitted_at')->nullable();

            // Cached official claim number populated after matching OrigClaimNo and company.
            $table->string('ClaimNo', 20)->nullable()->index();
            $table->unsignedBigInteger('linked_by')->nullable();
            $table->dateTime('linked_at')->nullable();
            $table->string('CoverNo', 50)->nullable()->index();

            // Preserve only the cedant's original submitted details here.
            $table->string('OrigPolicy', 250)->nullable();
            $table->string('OrigClaimNo', 50);
            $table->string('InsuredName', 250)->nullable();
            $table->string('LossPeriod', 250)->nullable();
            $table->string('DateLoss', 20)->nullable();
            $table->string('DateReported', 20)->nullable();
            $table->string('LossLocation', 250)->nullable();
            $table->string('LossDetails', 2000)->nullable();
            $table->string('Comments', 2000)->nullable();
            $table->decimal('ClaimAmt', 24, 8)->nullable();
            $table->string('ClaimCurrencyCode', 50)->nullable();
            $table->string('ClaimCurrencyName', 150)->nullable();

            // Official statuses, classifications and accounting values live in claims.
            $table->timestamps();
            $table->index(['company_code', 'portal_status']);
            $table->unique(['company_code', 'OrigClaimNo'], 'claim_submission_company_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_submissions');
    }
};
