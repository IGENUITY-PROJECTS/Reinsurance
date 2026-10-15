<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', fn (Blueprint $table) => $table->unsignedSmallInteger('portal_user_limit')->default(5));
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('access_status', 20)->default('pending')->index();
            $table->dateTime('access_expires_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
        });
        // Existing staff retain approval. Existing cedants require membership review; email verification is never bypassed.
        DB::table('users')->whereIn('id', DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_type', User::class)->whereIn('roles.name', ['admin', 'super-admin'])->select('model_id'))
            ->update(['access_status' => 'approved', 'approved_at' => now()]);
        DB::table('users')->where('access_status', 'pending')->update(['access_expires_at' => now()->addDays(7)]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropIndex(['access_status']);
            $table->dropColumn(['access_status', 'access_expires_at', 'approved_by', 'approved_at']);
        });
        Schema::table('companies', fn (Blueprint $table) => $table->dropColumn('portal_user_limit'));
    }
};
