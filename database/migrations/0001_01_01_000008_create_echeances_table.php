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
        Schema::create('echeances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_contrat')->constrained('contrats')->cascadeOnDelete();
            $table->date('date_echeance');
            $table->decimal('montant_prevu', 15, 2);
            $table->decimal('montant_paye', 15, 2)->default(0);
            $table->enum('statut', ['A_PAYER', 'PAYE', 'EN_RETARD'])->default('A_PAYER');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('echeances');
    }
};
