<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_commission_details', function (Blueprint $table) {
            $table->id();
            // Preserve source column names; source dates and integer flags retain their original types.
            $table->integer('DetailID')->nullable();
            $table->string('DocumentNo', 50)->nullable();
            $table->string('ItemCode', 50)->nullable();
            $table->string('ItemDescription', 150)->nullable();
            $table->integer('MOperator')->nullable();
            $table->decimal('Amount', 24, 8)->nullable();
            $table->integer('PrintOrder')->nullable();
            $table->decimal('MRate', 24, 8)->nullable();
            $table->integer('CompanyOption')->nullable();
            $table->string('CompanyCode', 50)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Source identity must be verified before adding unique sync keys.
            // No mirror foreign keys: headers, details, covers and companies can sync in any order.
            $table->index('DetailID');
            $table->index(['DocumentNo', 'PrintOrder']);
            $table->index('CompanyCode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_commission_details');
    }
};
