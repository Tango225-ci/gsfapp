<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Clients : CLI-2026-0001 → CLI-00001 ──────────────────────────────
        $clients = DB::table('clients')->orderBy('id')->get(['id']);
        foreach ($clients as $i => $client) {
            DB::table('clients')
                ->where('id', $client->id)
                ->update(['reference_client' => 'CLI-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT)]);
        }

        // ── Paiements : PAI-202607-0001 → PAI-00001 ──────────────────────────
        $paiements = DB::table('paiements')->orderBy('id_paiement')->get(['id_paiement']);
        foreach ($paiements as $i => $paiement) {
            DB::table('paiements')
                ->where('id_paiement', $paiement->id_paiement)
                ->update(['reference' => 'PAI-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        // Pas de rollback automatique possible sans sauvegarder les anciennes valeurs
    }
};
