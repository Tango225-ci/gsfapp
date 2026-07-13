<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versements_proprietaires', function (Blueprint $table) {
            $table->id('id_versement');
            $table->unsignedBigInteger('id_proprietaire');
            $table->decimal('montant', 15, 2);
            $table->date('date_versement');
            $table->string('reference', 50)->unique();
            $table->enum('mode_paiement', ['ESPECES', 'VIREMENT', 'CHEQUE', 'MOBILE_MONEY'])->default('ESPECES');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('id_utilisateur')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_proprietaire')
                  ->references('id_proprietaire')
                  ->on('proprietaires_terriens')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versements_proprietaires');
    }
};
