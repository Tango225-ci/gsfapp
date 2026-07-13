<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proprietaires_terriens', function (Blueprint $table) {
            $table->id('id_proprietaire');
            $table->string('nom', 200);
            $table->string('contact', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('adresse', 300)->nullable();
            $table->text('note')->nullable();
            $table->boolean('valeursupprimee')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proprietaires_terriens');
    }
};
