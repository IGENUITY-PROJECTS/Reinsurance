<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_commissions', function (Blueprint $table) {
            $table->id();
            // Preserve source column names; source dates and integer flags retain their original types.
            $table->string('DocNo', 100)->nullable();
            $table->integer('RecNo')->nullable();
            $table->string('RskNo', 20)->nullable();
            $table->string('RskDocCode', 20)->nullable();
            $table->string('RskDocDesc', 200)->nullable();
            $table->string('RskDate', 20)->nullable();
            $table->string('RskPerd', 10)->nullable();
            $table->string('RskYear', 10)->nullable();
            $table->string('RskQtr', 10)->nullable();
            $table->string('RskAcctTypeCode', 20)->nullable();
            $table->string('RskAcctTypeDesc', 200)->nullable();
            $table->string('CVNo', 50)->nullable();
            $table->string('CVRef', 200)->nullable();
            $table->string('CVBizTypeCode', 20)->nullable();
            $table->string('CVBizTypeDesc', 200)->nullable();
            $table->string('CVStartDate', 20)->nullable();
            $table->string('CVEndDate', 20)->nullable();
            $table->string('CVYear', 10)->nullable();
            $table->string('CVQtr', 10)->nullable();
            $table->string('CVCusCode', 20)->nullable();
            $table->string('CVCusName', 250)->nullable();
            $table->string('CVCusPostal', 250)->nullable();
            $table->string('CVCusTown', 250)->nullable();
            $table->string('CVCusCtryCode', 20)->nullable();
            $table->string('CVCusCtryDesc', 250)->nullable();
            $table->string('CVCusMktCode', 20)->nullable();
            $table->string('CVCusMktDesc', 250)->nullable();
            $table->string('REDivCode', 50)->nullable();
            $table->string('REDivDesc', 200)->nullable();
            $table->string('RETypeCode', 50)->nullable();
            $table->string('RETypeDesc', 200)->nullable();
            $table->string('REGrpCode', 50)->nullable();
            $table->string('REGrpDesc', 200)->nullable();
            $table->string('RECatCode', 50)->nullable();
            $table->string('RECatDesc', 200)->nullable();
            $table->string('RETreatyTypeCode', 50)->nullable();
            $table->string('RETreatyTypeDesc', 200)->nullable();
            $table->string('REClassCode', 50)->nullable();
            $table->string('REClassDesc', 200)->nullable();
            $table->string('CedCode', 20)->nullable();
            $table->string('CedName', 250)->nullable();
            $table->string('CedPostal', 250)->nullable();
            $table->string('CedTown', 250)->nullable();
            $table->string('CedCtryCode', 20)->nullable();
            $table->string('CedCtryDesc', 250)->nullable();
            $table->string('CedMktCode', 20)->nullable();
            $table->string('CedMktDesc', 250)->nullable();
            $table->string('CedCurnCode', 20)->nullable();
            $table->string('CedCurnDesc', 200)->nullable();
            $table->decimal('CedAmtFor', 24, 8)->nullable();
            $table->decimal('CedBrkShr', 24, 8)->nullable();
            $table->decimal('CedCompShr', 24, 8)->nullable();
            $table->decimal('CedOptShr', 24, 8)->nullable();
            $table->decimal('CommOptShr', 24, 8)->nullable();
            $table->decimal('BrkgOptShr', 24, 8)->nullable();
            $table->decimal('TotalTax', 24, 8)->nullable();
            $table->decimal('TotalComm', 24, 8)->nullable();
            $table->decimal('TotalBrkg', 24, 8)->nullable();
            $table->decimal('NetAmtCompShr', 24, 8)->nullable();
            $table->decimal('NetAmtOptShr', 24, 8)->nullable();
            $table->decimal('NetAmt', 24, 8)->nullable();
            $table->integer('MPosted')->nullable();
            $table->string('Comments', 2000)->nullable();
            $table->string('CreatedBy', 50)->nullable();
            $table->string('CreatedOn', 20)->nullable();
            $table->string('LastUser', 50)->nullable();
            $table->string('LastMaintained', 20)->nullable();
            $table->integer('MExport')->nullable();
            $table->integer('MExported')->nullable();
            $table->integer('MOperator')->nullable();
            $table->string('AcctCode', 50)->nullable();
            $table->string('AcctName', 250)->nullable();
            $table->string('AcctPostal', 250)->nullable();
            $table->string('AcctTown', 250)->nullable();
            $table->string('AcctMktCode', 50)->nullable();
            $table->string('AcctMktDesc', 250)->nullable();
            $table->string('AcctCurnCode', 20)->nullable();
            $table->string('AcctCurnDesc', 250)->nullable();
            $table->integer('LocCurnOper')->nullable();
            $table->string('AcctCtryCode', 50)->nullable();
            $table->string('AcctCtryDesc', 250)->nullable();
            $table->string('RskTypeCode', 20)->nullable();
            $table->string('RskTypeDesc', 100)->nullable();
            $table->string('RiderNo', 20)->nullable();
            $table->string('RiderDesc', 250)->nullable();
            $table->string('ScreenCode', 20)->nullable();
            $table->string('ClaimNo', 20)->nullable();
            $table->string('InterimNo', 20)->nullable();
            $table->decimal('RiskNoteAmount', 24, 8)->nullable();
            $table->decimal('AmountDueToRein', 24, 8)->nullable();
            $table->decimal('BalanceDiff', 24, 8)->nullable();
            $table->string('Balanced', 50)->nullable();
            $table->decimal('PCPercent', 24, 8)->nullable();
            $table->decimal('AmountBeforePC', 24, 8)->nullable();
            $table->integer('Committed')->nullable();
            $table->integer('InstallmentDate')->nullable();
            $table->integer('Reversed')->nullable();
            $table->string('ReversalReason', 400)->nullable();
            $table->string('ReversalDoc', 50)->nullable();
            $table->decimal('NetAmtB', 24, 8)->nullable();
            $table->integer('BatchNo')->nullable();
            $table->integer('EntryNo')->nullable();
            $table->decimal('InvoiceAmount', 24, 8)->nullable();
            $table->decimal('BrokerageEarned', 24, 8)->nullable();
            $table->decimal('ReinsurerAmount', 24, 8)->nullable();
            $table->decimal('ReinsurerBrokerage', 24, 8)->nullable();
            $table->decimal('ReinsurerFullAmount', 24, 8)->nullable();
            $table->decimal('AmountEntered', 24, 8)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Source identity must be verified before adding unique sync keys.
            // No mirror foreign keys: headers, details, covers and companies can sync in any order.
            $table->index('DocNo');
            $table->index('RecNo');
            $table->index('CVNo');
            $table->index('CedCode');
            $table->index('CVCusCode');
            $table->index('RskAcctTypeCode');
            $table->index('LastMaintained');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_commissions');
    }
};
