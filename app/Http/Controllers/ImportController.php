<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Client;
use App\Models\Lotissement;
use App\Models\Lot;
use App\Models\Contrat;
use App\Models\Paiement;
use App\Models\ProprietaireTerrien;
use App\Models\VersementProprietaire;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function index()
    {
        PermissionHelper::abort403('utilisateurs', 'view');
        return view('pages.import.import', [
            'title'     => 'Import CSV',
            'templates' => $this->getTemplates(),
        ]);
    }

    public function template(string $entity)
    {
        PermissionHelper::abort403('utilisateurs', 'view');

        $templates = $this->getTemplates();
        abort_if(!isset($templates[$entity]), 404);

        $tpl = $templates[$entity];

        return response()->streamDownload(function () use ($tpl) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($out, $tpl['headers'], ';');
            fputcsv($out, $tpl['example'], ';');
            fclose($out);
        }, 'canevas_' . $entity . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request, string $entity)
    {
        PermissionHelper::abort403('utilisateurs', 'view');

        $templates = $this->getTemplates();
        abort_if(!isset($templates[$entity]), 404);

        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $content = file_get_contents($request->file('file')->getRealPath());
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $lines = array_values(array_filter(explode("\n", $content), fn($l) => trim($l) !== ''));

        if (count($lines) < 2) {
            return response()->json(['success' => false, 'message' => 'Le fichier ne contient pas de données.'], 422);
        }

        $headers  = array_map('trim', str_getcsv($lines[0], ';'));
        $expected = $templates[$entity]['headers'];
        $missing  = array_diff($expected, $headers);

        if (!empty($missing)) {
            return response()->json([
                'success' => false,
                'message' => 'Colonnes manquantes : ' . implode(', ', $missing),
            ], 422);
        }

        // Mapping colonne → index dans le CSV
        $colIdx = [];
        foreach ($expected as $col) {
            $colIdx[$col] = array_search($col, $headers);
        }

        // Helper : lire une cellule et retourner null si vide
        $get = fn(array $row, string $col): ?string =>
            isset($colIdx[$col]) && isset($row[$colIdx[$col]]) && trim($row[$colIdx[$col]]) !== ''
                ? trim($row[$colIdx[$col]])
                : null;

        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach (array_slice($lines, 1) as $i => $line) {
                $row    = str_getcsv($line, ';');
                $lineNo = $i + 2;

                $res = $this->importRow($entity, $row, $get, $lineNo);

                if ($res['success']) {
                    $results['imported']++;
                } else {
                    $results['skipped']++;
                    $results['errors'][] = $res['error'];
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Import CSV $entity : " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success'  => true,
            'imported' => $results['imported'],
            'skipped'  => $results['skipped'],
            'errors'   => $results['errors'],
            'message'  => "{$results['imported']} ligne(s) importée(s), {$results['skipped']} ignorée(s).",
        ]);
    }

    private function importRow(string $entity, array $row, callable $get, int $line): array
    {
        try {
            return match ($entity) {
                'lotissements'            => $this->importLotissement($row, $get, $line),
                'proprietaires_terriens'  => $this->importProprietaire($row, $get, $line),
                'lots'                    => $this->importLot($row, $get, $line),
                'clients'                 => $this->importClient($row, $get, $line),
                'contrats'                => $this->importContrat($row, $get, $line),
                'paiements'               => $this->importPaiement($row, $get, $line),
                'versements_proprietaires' => $this->importVersementProprietaire($row, $get, $line),
                'visites'                 => $this->importVisite($row, $get, $line),
                default                   => ['success' => false, 'error' => "Ligne $line : entité inconnue."],
            };
        } catch (\Exception $e) {
            return ['success' => false, 'error' => "Ligne $line : " . $e->getMessage()];
        }
    }

    // ── Importateurs ─────────────────────────────────────────────────────────

    private function importLotissement(array $row, callable $get, int $line): array
    {
        $nom = $get($row, 'nom');
        if (!$nom) return ['success' => false, 'error' => "Ligne $line : nom obligatoire."];

        if (Lotissement::where('nom', $nom)->exists()) {
            return ['success' => false, 'error' => "Ligne $line : lotissement \"$nom\" existe déjà, ignoré."];
        }

        $validStatuts = ['NON_APPROUVE', 'APPROUVE', 'APPROUVE_DIFFUSE', 'ACD_INDIVIDUEL', 'ACD_GLOBAL'];
        $statut       = $get($row, 'statut_lotissement');

        Lotissement::create([
            'nom'                => $nom,
            'ville'              => $get($row, 'ville') ?? '',
            'localisation'       => $get($row, 'localisation'),
            'statut_lotissement' => in_array($statut, $validStatuts) ? $statut : 'NON_APPROUVE',
            'numero_approbation' => $get($row, 'numero_approbation'),
            'date_approbation'   => $get($row, 'date_approbation'),
            'valeursupprimee'    => 0,
        ]);

        return ['success' => true];
    }

    private function importProprietaire(array $row, callable $get, int $line): array
    {
        $nom = $get($row, 'nom');
        if (!$nom) return ['success' => false, 'error' => "Ligne $line : nom obligatoire."];

        if (ProprietaireTerrien::where('nom', $nom)->where('valeursupprimee', '!=', 1)->exists()) {
            return ['success' => false, 'error' => "Ligne $line : propriétaire \"$nom\" existe déjà, ignoré."];
        }

        ProprietaireTerrien::create([
            'nom'             => $nom,
            'contact'         => $get($row, 'contact'),
            'email'           => $get($row, 'email'),
            'adresse'         => $get($row, 'adresse'),
            'note'            => $get($row, 'note'),
            'valeursupprimee' => 0,
        ]);

        return ['success' => true];
    }

    private function importLot(array $row, callable $get, int $line): array
    {
        $num = $get($row, 'numero_lot');
        if (!$num) return ['success' => false, 'error' => "Ligne $line : numero_lot obligatoire."];

        if (Lot::where('numero_lot', $num)->exists()) {
            return ['success' => false, 'error' => "Ligne $line : lot \"$num\" existe déjà, ignoré."];
        }

        $idLotissement = null;
        $nomLot        = $get($row, 'nom_lotissement');
        if ($nomLot) {
            $lotissement = Lotissement::where('nom', $nomLot)->where('valeursupprimee', '!=', 1)->first();
            if (!$lotissement) {
                return ['success' => false, 'error' => "Ligne $line : lotissement \"$nomLot\" introuvable."];
            }
            $idLotissement = $lotissement->id_lotissement;
        }

        $idProprietaire = null;
        $nomProprietaire = $get($row, 'nom_proprietaire');
        if ($nomProprietaire) {
            $proprietaire = ProprietaireTerrien::where('nom', $nomProprietaire)->where('valeursupprimee', '!=', 1)->first();
            if (!$proprietaire) {
                return ['success' => false, 'error' => "Ligne $line : propriétaire terrien \"$nomProprietaire\" introuvable."];
            }
            $idProprietaire = $proprietaire->id_proprietaire;
        }

        $prixC = floatval($get($row, 'prix_comptant') ?? 0);
        $prixE = floatval($get($row, 'prix_echelonne') ?? 0);
        if ($prixC <= 0 && $prixE <= 0) {
            return ['success' => false, 'error' => "Ligne $line : au moins un prix doit être > 0."];
        }

        $validTypes   = ['RESIDENTIEL', 'COMMERCIAL', 'INDUSTRIEL', 'MIXTE'];
        $validStatuts = ['DISPONIBLE', 'RESERVE', 'VENDU'];
        $typeLot      = $get($row, 'type_lot');
        $statut       = $get($row, 'statut');

        Lot::create([
            'numero_lot'      => $num,
            'numero_ilot'     => $get($row, 'numero_ilot') ?? '',
            'type_lot'        => in_array($typeLot, $validTypes) ? $typeLot : null,
            'superficie'      => floatval($get($row, 'superficie') ?? 0) ?: null,
            'prix_comptant'   => $prixC,
            'prix_echelonne'  => $prixE,
            'prix_achat'      => floatval($get($row, 'prix_achat') ?? 0),
            'statut'          => in_array($statut, $validStatuts) ? $statut : 'DISPONIBLE',
            'id_lotissement'  => $idLotissement,
            'id_proprietaire' => $idProprietaire,
            'valeursupprimee' => 0,
        ]);

        return ['success' => true];
    }

    private function importClient(array $row, callable $get, int $line): array
    {
        $nom = $get($row, 'nom_prenoms');
        if (!$nom) return ['success' => false, 'error' => "Ligne $line : nom_prenoms obligatoire."];

        $email = $get($row, 'email');
        if ($email && Client::where('email', $email)->where('valeursupprimee', '!=', 1)->exists()) {
            return ['success' => false, 'error' => "Ligne $line : email \"$email\" déjà utilisé, ignoré."];
        }

        $validStatuts = ['ACTIF', 'SUSPENDU', 'PROSPECT'];
        $statut       = $get($row, 'statut');

        Client::create([
            'nom_prenoms'     => $nom,
            'contact'         => $get($row, 'contact') ?? '',
            'email'           => $email,
            'adresse'         => $get($row, 'adresse'),
            'piece_identite'  => $get($row, 'piece_identite'),
            'numero_piece'    => $get($row, 'numero_piece'),
            'date_validite'   => $get($row, 'date_validite'),
            'statut'          => in_array($statut, $validStatuts) ? $statut : 'ACTIF',
            'valeursupprimee' => 0,
        ]);

        return ['success' => true];
    }

    private function importContrat(array $row, callable $get, int $line): array
    {
        $nomClient = $get($row, 'nom_client');
        if (!$nomClient) return ['success' => false, 'error' => "Ligne $line : nom_client obligatoire."];

        $client = Client::where('nom_prenoms', $nomClient)->where('valeursupprimee', '!=', 1)->first();
        if (!$client) return ['success' => false, 'error' => "Ligne $line : client \"$nomClient\" introuvable."];

        $numLot = $get($row, 'numero_lot');
        if (!$numLot) return ['success' => false, 'error' => "Ligne $line : numero_lot obligatoire."];

        $lot = Lot::where('numero_lot', $numLot)->where('valeursupprimee', '!=', 1)->first();
        if (!$lot) return ['success' => false, 'error' => "Ligne $line : lot \"$numLot\" introuvable."];

        $contratExistant = Contrat::where('id_lot', $lot->id_lot)
            ->where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->exists();
        if ($contratExistant) {
            return ['success' => false, 'error' => "Ligne $line : lot \"$numLot\" déjà sous contrat, ignoré."];
        }

        $type = $get($row, 'type_paiement');
        if (!in_array($type, ['COMPTANT', 'ECHELONNE'])) {
            return ['success' => false, 'error' => "Ligne $line : type_paiement invalide (COMPTANT ou ECHELONNE)."];
        }

        $dateContrat = $get($row, 'date_contrat');
        $montant     = floatval($get($row, 'montant_total') ?? 0);
        if (!$dateContrat || $montant <= 0) {
            return ['success' => false, 'error' => "Ligne $line : date_contrat et montant_total obligatoires."];
        }

        $validStatuts = ['EN_COURS', 'SOLDE', 'RESILIE', 'EN_RETARD'];
        $validModes   = ['ESPECES', 'VIREMENT', 'CHEQUE', 'MOBILE_MONEY'];
        $validFreqs   = ['MENSUEL', 'TRIMESTRIEL', 'SEMESTRIEL', 'ANNUEL'];

        $statut = $get($row, 'statut');
        $mode   = $get($row, 'mode_paiement');
        $freq   = $get($row, 'frequence_paiement');
        $duree  = intval($get($row, 'duree_mois') ?? 1) ?: 1;

        Contrat::create([
            'id_client'          => $client->id,
            'id_lot'             => $lot->id_lot,
            'date_contrat'       => $dateContrat,
            'date_debut'         => $get($row, 'date_debut') ?? $dateContrat,
            'montant_total'      => $montant,
            'montant_verse'      => 0,
            'reste_a_payer'      => $montant,
            'type_paiement'      => $type,
            'duree_mois'         => $duree,
            'frequence_paiement' => in_array($freq, $validFreqs) ? $freq : null,
            'statut'             => in_array($statut, $validStatuts) ? $statut : 'EN_COURS',
            'mode_paiement'      => in_array($mode, $validModes) ? $mode : null,
            'banque_paiement'    => $get($row, 'banque_paiement'),
            'valeursupprimee'    => 0,
        ]);

        if ($lot->statut === 'DISPONIBLE') {
            $lot->update(['statut' => 'RESERVE']);
        }

        return ['success' => true];
    }

    private function importPaiement(array $row, callable $get, int $line): array
    {
        $refContrat = $get($row, 'reference_contrat');
        if (!$refContrat) return ['success' => false, 'error' => "Ligne $line : reference_contrat obligatoire."];

        $contrat = Contrat::where('reference_contrat', $refContrat)
            ->where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->first();
        if (!$contrat) return ['success' => false, 'error' => "Ligne $line : contrat \"$refContrat\" introuvable."];

        $date    = $get($row, 'date_paiement');
        $montant = floatval($get($row, 'montant') ?? 0);
        if (!$date || $montant <= 0) {
            return ['success' => false, 'error' => "Ligne $line : date_paiement et montant obligatoires."];
        }

        $validModes   = ['ESPECES', 'VIREMENT', 'CHEQUE', 'MOBILE_MONEY'];
        $validStatuts = ['EN_ATTENTE', 'VALIDE', 'ANNULE', 'REJETE'];
        $mode         = $get($row, 'mode_paiement');
        $statut       = $get($row, 'statut');

        if (!in_array($mode, $validModes)) {
            return ['success' => false, 'error' => "Ligne $line : mode_paiement invalide."];
        }

        $reference = $get($row, 'reference')
            ?? ('PAI-' . date('Ym') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT));

        Paiement::create([
            'id_contrat'      => $contrat->id_contrat,
            'date_paiement'   => $date,
            'montant'         => $montant,
            'mode_paiement'   => $mode,
            'banque_paiement' => $get($row, 'banque_paiement'),
            'statut'          => in_array($statut, $validStatuts) ? $statut : 'EN_ATTENTE',
            'reference'       => $reference,
            'notes'           => $get($row, 'notes'),
            'type_paiement'   => 'MENSUALITE',
            'valeursupprimee' => 0,
        ]);

        return ['success' => true];
    }

    private function importVersementProprietaire(array $row, callable $get, int $line): array
    {
        $nomProprietaire = $get($row, 'nom_proprietaire');
        if (!$nomProprietaire) return ['success' => false, 'error' => "Ligne $line : nom_proprietaire obligatoire."];

        $proprietaire = ProprietaireTerrien::where('nom', $nomProprietaire)->where('valeursupprimee', '!=', 1)->first();
        if (!$proprietaire) return ['success' => false, 'error' => "Ligne $line : propriétaire \"$nomProprietaire\" introuvable."];

        $date    = $get($row, 'date_versement');
        $montant = floatval($get($row, 'montant') ?? 0);
        if (!$date || $montant <= 0) {
            return ['success' => false, 'error' => "Ligne $line : date_versement et montant obligatoires."];
        }

        $validModes = ['ESPECES', 'VIREMENT', 'CHEQUE', 'MOBILE_MONEY'];
        $mode       = $get($row, 'mode_paiement');

        VersementProprietaire::create([
            'id_proprietaire'  => $proprietaire->id_proprietaire,
            'date_versement'   => $date,
            'montant'          => $montant,
            'mode_paiement'    => in_array($mode, $validModes) ? $mode : 'ESPECES',
            'banque'           => $get($row, 'banque'),
            'operateur'        => $get($row, 'operateur'),
            'reference_cheque' => $get($row, 'reference_cheque'),
            'reference'        => $get($row, 'reference'),
            'note'             => $get($row, 'note'),
        ]);

        return ['success' => true];
    }

    private function importVisite(array $row, callable $get, int $line): array
    {
        $nom = $get($row, 'nom_visiteur');
        if (!$nom) return ['success' => false, 'error' => "Ligne $line : nom_visiteur obligatoire."];

        $depart = $get($row, 'heure_depart');
        if (!$depart) return ['success' => false, 'error' => "Ligne $line : heure_depart obligatoire."];

        Visite::create([
            'nom_visiteur'     => $nom,
            'contact_visiteur' => $get($row, 'contact_visiteur') ?? '',
            'adresse_visiteur' => $get($row, 'adresse_visiteur') ?? '',
            'date_visite'      => $get($row, 'date_visite') ?? date('Y-m-d'),
            'heure_arrivee'    => $get($row, 'heure_arrivee'),
            'heure_depart'     => $depart,
            'motif'            => $get($row, 'motif') ?? '',
            'valeursupprimee'  => 0,
        ]);

        return ['success' => true];
    }

    // ── Définitions des canevas ───────────────────────────────────────────────

    private function getTemplates(): array
    {
        return [
            'lotissements' => [
                'label'   => 'Lotissements',
                'order'   => 1,
                'desc'    => 'Aucune dépendance — à importer en premier.',
                'headers' => ['nom', 'ville', 'localisation', 'statut_lotissement', 'numero_approbation', 'date_approbation'],
                'example' => ['Les Palmiers', 'Abidjan', 'Cocody, carrefour Lycée technique', 'APPROUVE', 'APP-2024-001', '2024-03-15'],
                'columns' => [
                    ['name' => 'nom',                'req' => true,  'format' => 'Texte',                                                                    'example' => 'Les Palmiers'],
                    ['name' => 'ville',              'req' => true,  'format' => 'Texte',                                                                    'example' => 'Abidjan'],
                    ['name' => 'localisation',       'req' => false, 'format' => 'Texte libre',                                                              'example' => 'Cocody, quartier résidentiel'],
                    ['name' => 'statut_lotissement', 'req' => false, 'format' => 'NON_APPROUVE | APPROUVE | APPROUVE_DIFFUSE | ACD_INDIVIDUEL | ACD_GLOBAL', 'example' => 'APPROUVE'],
                    ['name' => 'numero_approbation', 'req' => false, 'format' => 'Texte',                                                                    'example' => 'APP-2024-001'],
                    ['name' => 'date_approbation',   'req' => false, 'format' => 'AAAA-MM-JJ',                                                               'example' => '2024-03-15'],
                ],
            ],
            'proprietaires_terriens' => [
                'label'   => 'Propriétaires terriens',
                'order'   => 2,
                'desc'    => 'Aucune dépendance — importez-les avant les Lots si vous liez des lots à un propriétaire.',
                'headers' => ['nom', 'contact', 'email', 'adresse', 'note'],
                'example' => ['Kouadio Yao', '+22507000001', '', 'Azaguié', ''],
                'columns' => [
                    ['name' => 'nom',     'req' => true,  'format' => 'Texte (unique)', 'example' => 'Kouadio Yao'],
                    ['name' => 'contact', 'req' => false, 'format' => 'Téléphone',      'example' => '+22507000001'],
                    ['name' => 'email',   'req' => false, 'format' => 'Email',          'example' => ''],
                    ['name' => 'adresse', 'req' => false, 'format' => 'Texte libre',    'example' => 'Azaguié'],
                    ['name' => 'note',    'req' => false, 'format' => 'Texte libre',    'example' => ''],
                ],
            ],
            'lots' => [
                'label'   => 'Lots',
                'order'   => 3,
                'desc'    => 'Dépend des Lotissements (et des Propriétaires terriens si renseignés) — importez-les d\'abord.',
                'headers' => ['numero_lot', 'numero_ilot', 'nom_lotissement', 'type_lot', 'superficie', 'prix_comptant', 'prix_echelonne', 'prix_achat', 'nom_proprietaire', 'statut'],
                'example' => ['LOT-001', 'IL-01', 'Les Palmiers', 'RESIDENTIEL', '500', '15000000', '18000000', '8000000', 'Kouadio Yao', 'DISPONIBLE'],
                'columns' => [
                    ['name' => 'numero_lot',       'req' => true,  'format' => 'Texte unique',                                  'example' => 'LOT-001'],
                    ['name' => 'numero_ilot',      'req' => true,  'format' => 'Texte',                                        'example' => 'IL-01'],
                    ['name' => 'nom_lotissement',  'req' => false, 'format' => 'Nom exact du lotissement (clé étrangère)',      'example' => 'Les Palmiers'],
                    ['name' => 'type_lot',         'req' => false, 'format' => 'RESIDENTIEL | COMMERCIAL | INDUSTRIEL | MIXTE', 'example' => 'RESIDENTIEL'],
                    ['name' => 'superficie',       'req' => false, 'format' => 'Nombre en m²',                                 'example' => '500'],
                    ['name' => 'prix_comptant',    'req' => false, 'format' => 'Nombre en FCFA (au moins l\'un des 2 > 0)',    'example' => '15000000'],
                    ['name' => 'prix_echelonne',   'req' => false, 'format' => 'Nombre en FCFA',                               'example' => '18000000'],
                    ['name' => 'prix_achat',       'req' => false, 'format' => 'Nombre en FCFA (prix payé au propriétaire)',   'example' => '8000000'],
                    ['name' => 'nom_proprietaire', 'req' => false, 'format' => 'Nom exact du propriétaire terrien (clé étrangère)', 'example' => 'Kouadio Yao'],
                    ['name' => 'statut',           'req' => true,  'format' => 'DISPONIBLE | RESERVE | VENDU',                 'example' => 'DISPONIBLE'],
                ],
            ],
            'clients' => [
                'label'   => 'Clients',
                'order'   => 4,
                'desc'    => 'Aucune dépendance — peut être importé indépendamment.',
                'headers' => ['nom_prenoms', 'contact', 'email', 'adresse', 'piece_identite', 'numero_piece', 'date_validite', 'statut'],
                'example' => ['Kouassi Jean-Baptiste', '+22507000000', 'jean@email.com', 'Abidjan Cocody', 'CNI', 'CI0001234', '2026-12-31', 'ACTIF'],
                'columns' => [
                    ['name' => 'nom_prenoms',    'req' => true,  'format' => 'Texte',                          'example' => 'Kouassi Jean-Baptiste'],
                    ['name' => 'contact',        'req' => true,  'format' => 'Téléphone',                      'example' => '+22507000000'],
                    ['name' => 'email',          'req' => false, 'format' => 'Email (unique dans le système)', 'example' => 'jean@email.com'],
                    ['name' => 'adresse',        'req' => false, 'format' => 'Texte libre',                    'example' => 'Abidjan Cocody'],
                    ['name' => 'piece_identite', 'req' => false, 'format' => 'CNI | Passeport | ...',          'example' => 'CNI'],
                    ['name' => 'numero_piece',   'req' => false, 'format' => 'Texte',                          'example' => 'CI0001234'],
                    ['name' => 'date_validite',  'req' => false, 'format' => 'AAAA-MM-JJ',                     'example' => '2026-12-31'],
                    ['name' => 'statut',         'req' => true,  'format' => 'ACTIF | SUSPENDU | PROSPECT',    'example' => 'ACTIF'],
                ],
            ],
            'contrats' => [
                'label'   => 'Contrats',
                'order'   => 5,
                'desc'    => 'Dépend des Clients et des Lots — importez-les d\'abord.',
                'headers' => ['nom_client', 'numero_lot', 'date_contrat', 'date_debut', 'montant_total', 'type_paiement', 'duree_mois', 'frequence_paiement', 'statut', 'mode_paiement', 'banque_paiement'],
                'example' => ['Kouassi Jean-Baptiste', 'LOT-001', '2024-01-15', '2024-01-15', '15000000', 'ECHELONNE', '24', 'MENSUEL', 'EN_COURS', '', ''],
                'columns' => [
                    ['name' => 'nom_client',         'req' => true,  'format' => 'Nom exact du client (clé étrangère)',                   'example' => 'Kouassi Jean-Baptiste'],
                    ['name' => 'numero_lot',         'req' => true,  'format' => 'Numéro exact du lot (clé étrangère)',                   'example' => 'LOT-001'],
                    ['name' => 'date_contrat',       'req' => true,  'format' => 'AAAA-MM-JJ',                                           'example' => '2024-01-15'],
                    ['name' => 'date_debut',         'req' => false, 'format' => 'AAAA-MM-JJ (= date_contrat si vide)',                   'example' => '2024-01-15'],
                    ['name' => 'montant_total',      'req' => true,  'format' => 'Nombre en FCFA',                                       'example' => '15000000'],
                    ['name' => 'type_paiement',      'req' => true,  'format' => 'COMPTANT | ECHELONNE',                                 'example' => 'ECHELONNE'],
                    ['name' => 'duree_mois',         'req' => false, 'format' => 'Entier (nombre de mois)',                               'example' => '24'],
                    ['name' => 'frequence_paiement', 'req' => false, 'format' => 'MENSUEL | TRIMESTRIEL | SEMESTRIEL | ANNUEL',           'example' => 'MENSUEL'],
                    ['name' => 'statut',             'req' => true,  'format' => 'EN_COURS | SOLDE | RESILIE | EN_RETARD',               'example' => 'EN_COURS'],
                    ['name' => 'mode_paiement',      'req' => false, 'format' => 'ESPECES | VIREMENT | CHEQUE | MOBILE_MONEY',           'example' => ''],
                    ['name' => 'banque_paiement',    'req' => false, 'format' => 'Texte (banque/opérateur si applicable)',               'example' => ''],
                ],
            ],
            'paiements' => [
                'label'   => 'Paiements',
                'order'   => 6,
                'desc'    => 'Dépend des Contrats — importez-les d\'abord.',
                'headers' => ['reference_contrat', 'date_paiement', 'montant', 'mode_paiement', 'banque_paiement', 'statut', 'reference', 'notes'],
                'example' => ['CON-202401-0001', '2024-02-01', '625000', 'ESPECES', '', 'VALIDE', 'PAI-202402-0001', ''],
                'columns' => [
                    ['name' => 'reference_contrat', 'req' => true,  'format' => 'Référence du contrat (ex: CON-202401-0001)',  'example' => 'CON-202401-0001'],
                    ['name' => 'date_paiement',     'req' => true,  'format' => 'AAAA-MM-JJ',                                 'example' => '2024-02-01'],
                    ['name' => 'montant',           'req' => true,  'format' => 'Nombre en FCFA',                             'example' => '625000'],
                    ['name' => 'mode_paiement',     'req' => true,  'format' => 'ESPECES | VIREMENT | CHEQUE | MOBILE_MONEY', 'example' => 'ESPECES'],
                    ['name' => 'banque_paiement',   'req' => false, 'format' => 'Texte (si virement ou chèque)',              'example' => ''],
                    ['name' => 'statut',            'req' => true,  'format' => 'EN_ATTENTE | VALIDE | ANNULE | REJETE',      'example' => 'VALIDE'],
                    ['name' => 'reference',         'req' => false, 'format' => 'Texte (auto-généré si vide)',                'example' => 'PAI-202402-0001'],
                    ['name' => 'notes',             'req' => false, 'format' => 'Texte libre',                                'example' => ''],
                ],
            ],
            'versements_proprietaires' => [
                'label'   => 'Versements propriétaires',
                'order'   => 7,
                'desc'    => 'Dépend des Propriétaires terriens — importez-les d\'abord.',
                'headers' => ['nom_proprietaire', 'date_versement', 'montant', 'mode_paiement', 'banque', 'operateur', 'reference_cheque', 'reference', 'note'],
                'example' => ['Kouadio Yao', '2024-02-01', '2000000', 'ESPECES', '', '', '', '', ''],
                'columns' => [
                    ['name' => 'nom_proprietaire', 'req' => true,  'format' => 'Nom exact du propriétaire terrien (clé étrangère)', 'example' => 'Kouadio Yao'],
                    ['name' => 'date_versement',   'req' => true,  'format' => 'AAAA-MM-JJ',                                 'example' => '2024-02-01'],
                    ['name' => 'montant',          'req' => true,  'format' => 'Nombre en FCFA',                             'example' => '2000000'],
                    ['name' => 'mode_paiement',    'req' => false, 'format' => 'ESPECES | VIREMENT | CHEQUE | MOBILE_MONEY (ESPECES par défaut)', 'example' => 'ESPECES'],
                    ['name' => 'banque',           'req' => false, 'format' => 'Texte (si virement)',                        'example' => ''],
                    ['name' => 'operateur',        'req' => false, 'format' => 'Texte (si mobile money)',                    'example' => ''],
                    ['name' => 'reference_cheque', 'req' => false, 'format' => 'Texte (si chèque)',                          'example' => ''],
                    ['name' => 'reference',        'req' => false, 'format' => 'Texte (auto-généré si vide)',                'example' => ''],
                    ['name' => 'note',             'req' => false, 'format' => 'Texte libre',                                'example' => ''],
                ],
            ],
            'visites' => [
                'label'   => 'Visites',
                'order'   => 8,
                'desc'    => 'Aucune dépendance — peut être importé indépendamment.',
                'headers' => ['nom_visiteur', 'contact_visiteur', 'adresse_visiteur', 'date_visite', 'heure_arrivee', 'heure_depart', 'motif'],
                'example' => ['Diallo Ibrahim', '+22501234567', 'Yopougon', '2024-03-10', '09:30', '11:00', 'Visite terrain lot 001'],
                'columns' => [
                    ['name' => 'nom_visiteur',     'req' => true,  'format' => 'Texte',       'example' => 'Diallo Ibrahim'],
                    ['name' => 'contact_visiteur', 'req' => true,  'format' => 'Téléphone',   'example' => '+22501234567'],
                    ['name' => 'adresse_visiteur', 'req' => true,  'format' => 'Texte',       'example' => 'Yopougon'],
                    ['name' => 'date_visite',      'req' => true,  'format' => 'AAAA-MM-JJ',  'example' => '2024-03-10'],
                    ['name' => 'heure_arrivee',    'req' => false, 'format' => 'HH:MM',       'example' => '09:30'],
                    ['name' => 'heure_depart',     'req' => true,  'format' => 'HH:MM',       'example' => '11:00'],
                    ['name' => 'motif',            'req' => true,  'format' => 'Texte',       'example' => 'Visite terrain lot 001'],
                ],
            ],
        ];
    }
}
