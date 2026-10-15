<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aged_cedant_claim_payables', function (Blueprint $table) {
            $table->id();
            // Mirror APCreditorsClaimsAGED, including payments, journals and source signs.
            $table->integer('DetailID')->nullable();
            $table->string('DocumentNo', 50)->nullable();
            $table->dateTime('DocumentDate')->nullable();
            $table->integer('DocumentDateNo')->nullable();
            $table->string('AccountTypeCode', 50)->nullable();
            $table->string('AccountType', 150)->nullable();
            $table->decimal('MAmount', 24, 8)->nullable();
            $table->decimal('SettledAmount', 24, 8)->nullable();
            $table->decimal('PendingAmount', 24, 8)->nullable();
            $table->string('CurrencyCode', 50)->nullable();
            $table->string('CurrencyName', 100)->nullable();
            $table->decimal('ExchangeRate', 24, 8)->nullable();
            $table->decimal('FunctionalAmount', 24, 8)->nullable();
            $table->string('RiskNoteRef', 100)->nullable();
            $table->string('Insured', 250)->nullable();
            $table->string('RiskDetails', 200)->nullable();
            $table->string('CategoryType', 50)->nullable();
            $table->string('CategoryName', 150)->nullable();
            $table->string('CedantCode', 50)->nullable();
            $table->string('CedantName', 150)->nullable();
            $table->string('DocumentTypeCode', 50)->nullable();
            $table->string('DocumentType', 100)->nullable();
            $table->dateTime('DOL')->nullable();
            $table->integer('DOLNo')->nullable();
            $table->string('ClaimRefReinsured', 50)->nullable();
            $table->string('ClaimReference', 50)->nullable();
            $table->decimal('PercentPaid', 24, 8)->nullable();
            $table->string('RequisitionNo', 50)->nullable();
            $table->string('CollectionRemark', 50)->nullable();
            $table->integer('Reversed')->nullable();
            $table->string('ReversingDoc', 50)->nullable();
            $table->decimal('CRate', 24, 8)->nullable();
            $table->integer('PostingSeq')->nullable();
            $table->decimal('Revalued2021', 24, 8)->nullable();
            $table->decimal('RevaluationRate2021', 24, 8)->nullable();
            $table->integer('Picked')->nullable();

            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();

            // Lookup indexes only; source upsert keys will be confirmed separately.
            $table->index('DetailID');
            $table->index('DocumentNo');
            $table->index(['CedantCode', 'DocumentDate']);
            $table->index('RiskNoteRef');
            $table->index('PostingSeq');
            $table->index('ClaimReference');
            $table->index('ClaimRefReinsured');
            // No source foreign keys: historical account/company rows may arrive later.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aged_cedant_claim_payables');
    }
};
