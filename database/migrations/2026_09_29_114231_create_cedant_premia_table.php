<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cedant_premiums', function (Blueprint $table) {
            $table->id();
            // Preserve source values, including settled/pending amounts and reversals.
            $table->string('DocumentNo', 50)->nullable();
            $table->dateTime('DocumentDate')->nullable();
            $table->integer('DocumentDateNo')->nullable();
            $table->string('AccountTypeCode', 50)->nullable();
            $table->string('AccountType', 150)->nullable();
            $table->double('MAmount')->nullable();
            $table->double('SettledAmount')->nullable();
            $table->double('PendingAmount')->nullable();
            $table->string('CurrencyCode', 50)->nullable();
            $table->string('CurrencyName', 100)->nullable();
            $table->double('ExchangeRate')->nullable();
            $table->double('FunctionalAmount')->nullable();
            $table->string('RiskNoteRef', 100)->nullable();
            $table->string('Insured', 250)->nullable();
            $table->string('RiskDetails', 200)->nullable();
            $table->string('LastUser', 50)->nullable();
            $table->dateTime('LastMaintained')->nullable();
            $table->integer('LastMaintainedNo')->nullable();
            $table->string('CategoryType', 50)->nullable();
            $table->string('CategoryName', 150)->nullable();
            $table->string('CedantCode', 50)->nullable();
            $table->string('CedantName', 150)->nullable();
            $table->string('DocumentTypeCode', 50)->nullable();
            $table->string('DocumentType', 100)->nullable();
            $table->double('PercentPaid')->nullable();
            $table->double('Brokerage')->nullable();
            $table->double('ReinsurersAmount')->nullable();
            $table->string('CollectionRemark', 50)->nullable();
            $table->integer('Reversed')->nullable();
            $table->string('ReversingDoc', 50)->nullable();
            $table->double('CRate')->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();
            // Confirm the source document key before adding a unique constraint.
            $table->index('DocumentNo');
            $table->index(['CedantCode', 'DocumentDate']);
            $table->index('RiskNoteRef');
            // No foreign keys: companies can arrive after premium records.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cedant_premiums');
    }
};
