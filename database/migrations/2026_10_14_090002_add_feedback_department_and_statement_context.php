<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback_conversations', function (Blueprint $table) {
            $table->string('topic', 30)->default('general');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('needs_assignment')->default(true);
            $table->string('statement_kind', 20)->nullable();
            $table->unsignedBigInteger('statement_document_id')->nullable();
            $table->string('statement_document_no', 50)->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->string('statement_currency', 50)->nullable();
            $table->string('statement_search', 150)->nullable();
            $table->index(['department_id', 'updated_at'], 'feedback_department_updated_index');
        });
    }

    public function down(): void
    {
        Schema::table('feedback_conversations', function (Blueprint $table) {
            $table->dropIndex('feedback_department_updated_index');
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['topic', 'needs_assignment', 'statement_kind', 'statement_document_id', 'statement_document_no', 'period_from', 'period_to', 'statement_currency', 'statement_search']);
        });
    }
};
