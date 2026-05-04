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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_client')->constrained('clients');
            $table->unsignedBigInteger('id_lot')->unique();
            $table->date('date_contrat');
            $table->decimal('montant_total', 15, 2);
            $table->enum('type_paiement', ['COMPTANT', 'ECHELONNE']);
            $table->enum('statut', ['EN_COURS', 'SOLDE', 'RESILIE', 'EN_RETARD'])->default('EN_COURS');
            $table->timestamp('created_at')->useCurrent();
            $table->index('id_client', 'idx_contrat_client');

            $table->foreign('id_lot')->references('id')->on('lots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
