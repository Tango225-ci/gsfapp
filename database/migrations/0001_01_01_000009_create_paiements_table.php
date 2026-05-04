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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_contrat')->constrained('contrats');
            $table->date('date_paiement');
            $table->decimal('montant', 15, 2);
            $table->string('mode_paiement', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'VALIDE', 'ANNULE', 'REJETE'])->default('VALIDE');
            $table->timestamp('created_at')->useCurrent();
            $table->index('id_contrat', 'idx_paiement_contrat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
