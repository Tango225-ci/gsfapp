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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom_prenoms', 200);
            $table->string('contact', 50);
            $table->string('email', 150)->nullable();
            $table->text('adresse')->nullable();
            $table->string('piece_identite', 100)->nullable();
            $table->string('numero_piece', 100)->nullable();
            $table->date('date_validite')->nullable();
            $table->decimal('solde_compte', 15, 2)->default(0);
            $table->enum('statut', ['ACTIF', 'SUSPENDU', 'PROSPECT'])->default('PROSPECT');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
