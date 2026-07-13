<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->unsignedBigInteger('id_proprietaire')->nullable()->after('id_lotissement');
            $table->decimal('prix_achat', 15, 2)->default(0)->after('prix_echelonne');

            $table->foreign('id_proprietaire')
                  ->references('id_proprietaire')
                  ->on('proprietaires_terriens')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['id_proprietaire']);
            $table->dropColumn(['id_proprietaire', 'prix_achat']);
        });
    }
};
