<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('DocumentNo', 50)->nullable();
            $table->string('RskNo', 50)->nullable();
            $table->string('CoverNo', 50)->nullable();
            $table->string('CoverNoDesc', 50)->nullable();
            $table->string('CedantAccountNo', 50)->nullable();
            $table->string('Cedant', 250)->nullable();
            $table->string('DocCurrency', 50)->nullable();
            $table->string('AccountType', 50)->nullable();
            $table->string('AccountTypeDesc', 200)->nullable();
            $table->string('LastUser', 50)->nullable();
            $table->string('ItemNo', 50)->nullable();
            $table->string('ItemDesc', 150)->nullable();
            $table->string('Remarks', 450)->nullable();
            $table->double('MinDepositPremium')->nullable();
            $table->double('PremiumAdjRate')->nullable();
            $table->double('NetAccountedPremium')->nullable();
            $table->double('PremiumComputed')->nullable();
            $table->double('Claims')->nullable();
            $table->double('MinCost')->nullable();
            $table->double('MaxCost')->nullable();
            $table->double('LoadingFactor')->nullable();
            $table->double('LoadingFactorDiv')->nullable();
            $table->double('EffectiveRate')->nullable();
            $table->double('CedCommRate')->nullable();
            $table->double('PremiumSplit')->nullable();
            $table->integer('HasBurningCost')->nullable();
            $table->integer('MOperator')->nullable();
            $table->integer('PrintOrder')->nullable();
            $table->integer('Posted')->nullable();
            $table->integer('BasedOn')->nullable();
            $table->integer('Committed')->nullable();
            $table->integer('DeductMDP')->nullable();
            $table->dateTime('DocumentDate')->nullable();
            $table->dateTime('LastMaintained')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            // Document/item identity must be verified before introducing a unique sync key.
            $table->index('CoverNo');
            $table->index(['DocumentNo', 'ItemNo']);
            $table->index('CedantAccountNo');
            $table->index('LastMaintained');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_adjustments');
    }
};
