<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un client supprimé (valeursupprimee = 1) ne doit pas empêcher la
     * création d'un nouveau client avec le même contact/email. L'unicité
     * est désormais vérifiée côté application uniquement parmi les clients
     * actifs (valeursupprimee != 1), comme pour les autres entités.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_contact_unique');
            $table->dropUnique('clients_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unique('contact');
            $table->unique('email');
        });
    }
};
