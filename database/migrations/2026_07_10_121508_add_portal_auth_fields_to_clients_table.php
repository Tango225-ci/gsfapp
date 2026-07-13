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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('password')->nullable()->after('statut');
            $table->boolean('must_change_password')->default(true)->after('password');
            $table->timestamp('portal_account_created_at')->nullable()->after('must_change_password');
            $table->rememberToken()->after('portal_account_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['password', 'must_change_password', 'portal_account_created_at', 'remember_token']);
        });
    }
};
