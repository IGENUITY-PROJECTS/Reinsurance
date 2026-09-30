<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('covers', function (Blueprint $table) {
            $table->id();


            $table->string('CoverNo', 50)->unique();
            $table->integer('RecNo')->nullable();
            $table->string('MRef', 100)->nullable();
            $table->string('MTitle', 200)->nullable();
            $table->string('BizType', 20)->nullable();
            $table->string('CusCode', 50);
            $table->string('CusName', 200)->nullable();
            $table->string('CVStartDate', 20)->nullable();
            $table->string('CVEndDate', 20)->nullable();
            $table->string('CVDocDate', 20)->nullable();
            $table->string('CVYear', 20)->nullable();
            $table->string('REDivCode', 50)->nullable();
            $table->string('REDivDesc', 200)->nullable();
            $table->string('RETypeCode', 50)->nullable();
            $table->string('RETypeDesc', 200)->nullable();
            $table->string('REGrpCode', 50)->nullable();
            $table->string('REGrpDesc', 200)->nullable();
            $table->string('RECatCode', 50)->nullable();
            $table->string('RECatDesc', 200)->nullable();
            $table->string('RETreatyTypCode', 50)->nullable();
            $table->string('RETreatyTypDesc', 200)->nullable();
            $table->string('REClassCode', 50)->nullable();
            $table->string('REClassDesc', 200)->nullable();
            $table->string('InsCode', 50)->nullable();
            $table->string('InsName', 200)->nullable();
            $table->string('CVDetails', 2000)->nullable();
            $table->integer('MStatus')->nullable();
            $table->double('RskInsured')->nullable();
            $table->string('OrigCoverNo', 20)->nullable();
            $table->string('MReinsurers', 1000)->nullable();
            $table->double('MPremium')->nullable();
            $table->double('MBrokerage')->nullable();
            $table->string('MCurrency', 100)->nullable();
            $table->double('MShare')->nullable();
            $table->double('CashCallLimitAmount')->nullable();
            $table->double('CashCallExcessOff')->nullable();
            $table->double('CoverAmount')->nullable();

            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();


            $table->index(['CusCode', 'CVYear']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('covers');
    }
};
