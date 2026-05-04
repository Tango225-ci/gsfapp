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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versements_bancaires');
    }
};
