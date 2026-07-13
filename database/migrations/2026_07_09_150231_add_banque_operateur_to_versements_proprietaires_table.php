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
        Schema::table('versements_proprietaires', function (Blueprint $table) {
            $table->string('banque', 100)->nullable()->after('mode_paiement');
            $table->string('operateur', 100)->nullable()->after('banque');
            $table->string('reference_cheque', 100)->nullable()->after('operateur');
        });
    }

    public function down(): void
    {
        Schema::table('versements_proprietaires', function (Blueprint $table) {
            $table->dropColumn(['banque', 'operateur', 'reference_cheque']);
        });
    }
};
