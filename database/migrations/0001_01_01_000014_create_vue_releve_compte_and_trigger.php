<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE VIEW vue_releve_compte AS
SELECT
    c.id AS client_id,
    c.nom_prenoms,
    m.created_at,
    m.type,
    m.montant,
    m.origine,
    m.reference
FROM mouvements_compte m
JOIN clients c ON c.id = m.id_client;
SQL
        );

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_valider_versement
AFTER UPDATE ON versements_bancaires
FOR EACH ROW
BEGIN
    IF OLD.statut = 'EN_ATTENTE' AND NEW.statut = 'VALIDE' THEN
        UPDATE clients
        SET solde_compte = solde_compte + NEW.montant
        WHERE id = NEW.id_client;

        INSERT INTO mouvements_compte (
            id_client,
            type,
            montant,
            origine,
            reference
        ) VALUES (
            NEW.id_client,
            'CREDIT',
            NEW.montant,
            'VERSEMENT_BANCAIRE',
            NEW.reference
        );
    END IF;
END;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS vue_releve_compte');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_valider_versement');
    }
};
