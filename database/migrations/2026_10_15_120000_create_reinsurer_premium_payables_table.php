<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reinsurer_premium_payables', function (Blueprint $table) {
            $table->id();
            // Preserve APCreditorsPremiums names, including ReinsureCode as supplied by RBS.
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
            $table->string('ReinsureCode', 50)->nullable();
            $table->string('ReinsurerName', 150)->nullable();
            $table->string('DocumentTypeCode', 50)->nullable();
            $table->string('DocumentType', 100)->nullable();
            $table->string('CedantDoc', 50)->nullable();
            $table->decimal('PercentPaid', 24, 8)->nullable();
            $table->string('CedantCode', 50)->nullable();
            $table->string('CedantName', 250)->nullable();
            $table->string('RequisitionNo', 50)->nullable();
            $table->integer('Reversed')->nullable();
            $table->string('ReversingDoc', 50)->nullable();
            $table->decimal('CRate', 24, 8)->nullable();

            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();

            // Source uniqueness must be checked before choosing an upsert key.
            $table->index('DetailID');
            $table->index('DocumentNo');
            $table->index(['ReinsureCode', 'DocumentDate']);
            $table->index(['CedantCode', 'DocumentDate']);
            $table->index('CedantDoc');
            $table->index('RiskNoteRef');
            // No source foreign keys: companies and documents may sync later.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reinsurer_premium_payables');
    }
};
