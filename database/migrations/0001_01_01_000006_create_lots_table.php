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
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('reference_lot', 100)->unique();
            $table->string('type_lot', 50)->nullable();
            $table->decimal('superficie', 10, 2)->nullable();
            $table->decimal('prix_total', 15, 2);
            $table->enum('statut', ['DISPONIBLE', 'RESERVE', 'VENDU'])->default('DISPONIBLE');
            $table->unsignedBigInteger('id_lotissement')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_lotissement')->references('id')->on('lotissements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
