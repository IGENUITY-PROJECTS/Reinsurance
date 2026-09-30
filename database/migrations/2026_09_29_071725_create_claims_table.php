<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            // RBS mirror; verify ClaimNo is unique and nonblank before syncing.
            $table->string('ClaimNo', 20)->unique();
            $table->integer('ClaimRec')->nullable();
            $table->string('ClaimType', 20)->nullable();
            $table->string('RelatedNo', 20)->nullable();
            $table->string('RelatedRef', 100)->nullable();
            $table->string('ClaimRef', 100)->nullable();
            $table->string('CoverNo', 20)->nullable();
            $table->string('CoverRef', 100)->nullable();
            $table->string('CedCode', 50)->nullable();
            $table->string('CedName', 250)->nullable();
            $table->string('OrigPolicy', 250)->nullable();
            $table->string('OrigClaimNo', 50)->nullable();
            $table->string('InsuredName', 250)->nullable();
            $table->string('LossPeriod', 250)->nullable();
            $table->string('DateLoss', 20)->nullable();
            $table->string('DateRegistered', 20)->nullable();
            $table->string('LossLocation', 250)->nullable();
            $table->string('MStatusCode', 20)->nullable();
            $table->string('MStatusDesc', 250)->nullable();
            $table->string('LossDetails', 2000)->nullable();
            $table->string('Comments', 2000)->nullable();
            $table->string('CreatedOn', 20)->nullable();
            $table->string('CusCode', 50)->nullable();
            $table->string('CusName', 250)->nullable();
            $table->string('CVStartDate', 20)->nullable();
            $table->string('CVEndDate', 20)->nullable();
            $table->string('CVYear', 20)->nullable();
            $table->string('REDivCode', 50)->nullable();
            $table->string('REDivDesc', 250)->nullable();
            $table->string('RETypeCode', 50)->nullable();
            $table->string('RETypeDesc', 250)->nullable();
            $table->string('REGrpCode', 50)->nullable();
            $table->string('REGrpDesc', 500)->nullable();
            $table->string('RECatCode', 50)->nullable();
            $table->string('RECatDesc', 500)->nullable();
            $table->string('RETreatyTypCode', 50)->nullable();
            $table->string('RETreatyTypDesc', 250)->nullable();
            $table->string('REClassCode', 50)->nullable();
            $table->string('REClassDesc', 500)->nullable();
            $table->string('AcctCode', 50)->nullable();
            $table->string('AcctName', 250)->nullable();
            $table->string('AcctPostal', 250)->nullable();
            $table->string('AcctTown', 250)->nullable();
            $table->string('AcctCtryCode', 20)->nullable();
            $table->string('AcctCtryDesc', 250)->nullable();
            $table->string('AcctMktCode', 20)->nullable();
            $table->string('AcctMktDesc', 250)->nullable();
            $table->double('ClaimAmt')->nullable();
            $table->double('BrkClaimAmt')->nullable();
            $table->string('TreatyText', 500)->nullable();
            $table->double('LossReserve')->nullable();
            $table->double('CedantRetention')->nullable();
            $table->string('DateReported', 20)->nullable();
            $table->double('LogicalClaimAmount')->nullable();
            $table->integer('OldDocument')->nullable();
            $table->string('ClaimCurrencyCode', 50)->nullable();
            $table->string('ClaimCurrencyName', 150)->nullable();

            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();
            $table->index('CoverNo');
            $table->index('CedCode');
            $table->index('CusCode');
            $table->index(['OrigClaimNo', 'CedCode'], 'claim_original_reference_company_index');
            // Source relationships are resolved in models, allowing any sync order.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
