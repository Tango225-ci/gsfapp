<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Le serveur MySQL (WAMP) crée les tables en MyISAM par défaut : pas de
 * transactions (DB::transaction n'annule rien) ni de clés étrangères.
 * Conversion de toutes les tables de la base en InnoDB.
 * (config/database.php force désormais InnoDB pour les nouvelles tables.)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tablesWithEngine('MyISAM') as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
        }
    }

    public function down(): void
    {
        // Pas de retour à MyISAM : cela ferait perdre les transactions.
    }

    private function tablesWithEngine(string $engine): array
    {
        return array_map(fn ($r) => $r->TABLE_NAME, DB::select(
            "SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE = ?",
            [$engine]
        ));
    }
};
