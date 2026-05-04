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
        Schema::create('journaux_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs');
            $table->text('action');
            $table->text('ancienne_valeur')->nullable();
            $table->text('nouvelle_valeur')->nullable();
            $table->string('adresse_ip', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journaux_actions');
    }
};
