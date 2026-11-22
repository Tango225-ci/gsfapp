<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les versements bancaires ne sont plus rattachés aux clients : ils servent
 * désormais à suivre le(s) compte(s) bancaire(s) de l'entreprise GSF
 * (entrées ET sorties, avec validation ADMIN / DIRECTEUR).
 *
 * - suppression du trigger qui créditait clients.solde_compte
 * - versements_bancaires (jamais utilisée, vide) → operations_bancaires
 * - comptes_bancaires : solde initial + statut actif
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_valider_versement');

        Schema::table('comptes_bancaires', function (Blueprint $table) {
            if (!Schema::hasColumn('comptes_bancaires', 'valeursupprimee')) {
                $table->boolean('valeursupprimee')->default(false);
            }
            $table->decimal('solde_initial', 15, 2)->default(0)->after('intitule');
            $table->date('date_solde_initial')->nullable()->after('solde_initial');
            $table->boolean('actif')->default(true)->after('date_solde_initial');
        });

        Schema::dropIfExists('versements_bancaires');

        Schema::create('operations_bancaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_compte')->constrained('comptes_bancaires');
            $table->enum('type', ['CREDIT', 'DEBIT']);
            $table->string('categorie', 50);
            $table->string('libelle', 255);
            $table->decimal('montant', 15, 2);
            $table->date('date_operation');
            $table->string('reference', 100)->nullable();
            $table->text('note')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'VALIDE', 'REJETE'])->default('EN_ATTENTE');
            $table->string('motif_rejet', 255)->nullable();
            $table->unsignedBigInteger('id_utilisateur')->nullable();
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();

            $table->index(['id_compte', 'statut', 'date_operation'], 'idx_operation_compte');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_bancaires');

        Schema::create('versements_bancaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_client')->constrained('clients');
            $table->foreignId('id_compte')->constrained('comptes_bancaires');
            $table->date('date_versement');
            $table->decimal('montant', 15, 2);
            $table->string('reference', 100)->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'VALIDE'])->default('EN_ATTENTE');
            $table->timestamp('created_at')->useCurrent();
            $table->index('id_client', 'idx_versement_client');
        });

        Schema::table('comptes_bancaires', function (Blueprint $table) {
            $table->dropColumn(['solde_initial', 'date_solde_initial', 'actif']);
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_valider_versement
AFTER UPDATE ON versements_bancaires
FOR EACH ROW
BEGIN
    IF OLD.statut = 'EN_ATTENTE' AND NEW.statut = 'VALIDE' THEN
        UPDATE clients
        SET solde_compte = solde_compte + NEW.montant
        WHERE id = NEW.id_client;

        INSERT INTO mouvements_compte (id_client, type, montant, origine, reference)
        VALUES (NEW.id_client, 'CREDIT', NEW.montant, 'VERSEMENT_BANCAIRE', NEW.reference);
    END IF;
END;
SQL
        );
    }
};
