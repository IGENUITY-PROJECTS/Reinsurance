<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('CmpCode', 50)->unique();

            $table->string('CmpSubTypeCode', 20)->nullable();
            $table->string('CmpSubTypeDesc', 100)->nullable();
            $table->string('CmpTypeCode', 20)->nullable()->index();
            $table->string('CmpTypeDesc', 100)->nullable();

            $table->string('CompanyName', 200)->nullable();
            $table->string('ShortName', 50)->nullable();
            $table->string('PostalAdd', 200)->nullable();
            $table->string('MTown', 100)->nullable();

            $table->string('CountryCode', 50)->nullable();
            $table->string('CountryName', 100)->nullable();

            $table->string('MktZoneCode', 50)->nullable();
            $table->string('MktZoneDesc', 200)->nullable();

            $table->string('MFax', 100)->nullable();
            $table->string('EmailAdd', 200)->nullable();
            $table->string('Website', 200)->nullable();


            $table->string('StartDate', 20)->nullable();
            $table->string('CreatedOn', 20)->nullable();


            $table->integer('MActive')->nullable();

            $table->string('ZoneCode', 50)->nullable();
            $table->string('ZoneName', 50)->nullable();

            $table->string('NatAcNo', 50)->nullable();
            $table->string('NatAcName', 50)->nullable();
            $table->string('PinNo', 50)->nullable();

            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
