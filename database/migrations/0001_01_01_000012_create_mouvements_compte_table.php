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
        Schema::create('mouvements_compte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_client')->constrained('clients');
            $table->enum('type', ['CREDIT', 'DEBIT']);
            $table->decimal('montant', 15, 2);
            $table->string('origine', 100)->nullable();
            $table->string('reference', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('id_client', 'idx_mouvement_client');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements_compte');
    }
};
