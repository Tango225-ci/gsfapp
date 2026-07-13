<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('reference_client', 20)->nullable()->unique()->after('id');
        });

        // Backfill des clients existants, séquence par année basée sur l'ordre des IDs
        $clients = DB::table('clients')
            ->whereNull('reference_client')
            ->orderBy('id')
            ->get(['id', 'created_at']);

        $counters = [];
        foreach ($clients as $client) {
            $year = $client->created_at
                ? date('Y', strtotime($client->created_at))
                : date('Y');
            $counters[$year] = ($counters[$year] ?? 0) + 1;
            $ref = 'CLI-' . $year . '-' . str_pad($counters[$year], 4, '0', STR_PAD_LEFT);
            DB::table('clients')->where('id', $client->id)->update(['reference_client' => $ref]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('reference_client');
        });
    }
};
