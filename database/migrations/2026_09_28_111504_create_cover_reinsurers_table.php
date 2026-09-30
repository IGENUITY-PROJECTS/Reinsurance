<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_reinsurers', function (Blueprint $table) {
            $table->id();

          
            $table->integer('DetailID');
            $table->string('CoverNo', 20);
            $table->string('CedCode', 50)->nullable();
            $table->string('CedName', 200)->nullable();
            $table->string('ReinCode', 50)->nullable();
            $table->string('ReinName', 200)->nullable();
            $table->string('ReinPostal', 200)->nullable();
            $table->string('ReinTown', 100)->nullable();
            $table->string('ReinCtryCode', 50)->nullable();
            $table->string('ReinCtry', 100)->nullable();
            $table->string('ReinMktCode', 50)->nullable();
            $table->string('ReinMktDesc', 100)->nullable();
            $table->double('ShrAlloc')->nullable();
            $table->double('ShrComp')->nullable();
            $table->double('ShrOpt')->nullable();
            $table->double('BrkgComp')->nullable();
            $table->integer('MTaxable')->nullable();
            $table->integer('MTaxCompShr')->nullable();
            $table->integer('MTaxOptShr')->nullable();
            $table->double('RskInsured')->nullable();
            $table->double('CompAmt')->nullable();
            $table->double('OptAmt')->nullable();
            $table->double('TotalAmt')->nullable();
            $table->double('BrkgOpt')->nullable();
            $table->double('CommissionOverWride')->nullable();
            $table->double('BrokerageOverWride')->nullable();
            $table->double('BrokerageComp')->nullable();
            $table->double('BrokerageOpt')->nullable();

            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();

            // Validate this source key before importing.
            $table->unique(['CoverNo', 'DetailID']);
            $table->index('CedCode');
            $table->index('ReinCode');

            // No foreign keys: related data may arrive in a later sync.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_reinsurers');
    }
};