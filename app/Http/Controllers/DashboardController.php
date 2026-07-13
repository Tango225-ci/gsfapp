<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lot;
use App\Models\Lotissement;
use App\Models\Contrat;
use App\Models\Paiement;
use App\Models\Echeance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now        = Carbon::now();
        $today      = Carbon::today();
        $debutMois  = $now->copy()->startOfMonth();
        $finMois    = $now->copy()->endOfMonth();

        // ── Requête de base paiements terrain validés ─────────────────────────
        $baseP = fn() => Paiement::where('statut', 'VALIDE')
            ->where('type_paiement', '!=', 'FRAIS_DOCUMENT')
            ->where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'));

        // ── Encaissements ──────────────────────────────────────────────────────
        $encaisseAujourdhui = $baseP()->whereDate('date_paiement', $today)->sum('montant');
        $encaisseMois       = $baseP()->whereBetween('date_paiement', [$debutMois, $finMois])->sum('montant');
        $encaisseTotal      = $baseP()->sum('montant');

        // ── Encaissements journaliers (14 derniers jours) ─────────────────────
        $paiementsJournaliers = [];
        for ($i = 13; $i >= 0; $i--) {
            $jour    = $today->copy()->subDays($i);
            $montant = $baseP()->whereDate('date_paiement', $jour)->sum('montant');
            $paiementsJournaliers[] = [
                'label'   => $jour->locale('fr')->isoFormat('D MMM'),
                'montant' => floatval($montant),
            ];
        }

        // ── Encaissements par semaine (6 dernières semaines) ──────────────────
        $paiementsParSemaine = [];
        for ($i = 5; $i >= 0; $i--) {
            $debut   = $today->copy()->startOfWeek()->subWeeks($i);
            $fin     = $debut->copy()->endOfWeek();
            $montant = $baseP()->whereBetween('date_paiement', [$debut, $fin])->sum('montant');
            $paiementsParSemaine[] = [
                'label'   => 'S' . $debut->isoWeek() . ' ' . $debut->format('d/m'),
                'montant' => floatval($montant),
            ];
        }

        // ── Répartition par mode de paiement (mois courant) ──────────────────
        $modesLabels = [
            'ESPECES'      => 'Espèces',
            'VIREMENT'     => 'Virement',
            'CHEQUE'       => 'Chèque',
            'MOBILE_MONEY' => 'Mobile Money',
        ];
        $paiementsParMode = [];
        foreach ($modesLabels as $code => $label) {
            $montant = $baseP()
                ->where('mode_paiement', $code)
                ->whereBetween('date_paiement', [$debutMois, $finMois])
                ->sum('montant');
            $paiementsParMode[] = ['label' => $label, 'montant' => floatval($montant), 'code' => $code];
        }

        // ── Lots ──────────────────────────────────────────────────────────────
        $lotsQ = fn() => Lot::where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'));
        $totalLots       = $lotsQ()->count();
        $lotsDisponibles = $lotsQ()->where('statut', 'DISPONIBLE')->count();
        $lotsReserves    = $lotsQ()->where('statut', 'RESERVE')->count();
        $lotsVendus      = $lotsQ()->where('statut', 'VENDU')->count();

        // ── Lots par lotissement ──────────────────────────────────────────────
        $lotsParLotissement = Lotissement::where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('nom')
            ->get()
            ->map(function ($loti) {
                $lots = Lot::where('id_lotissement', $loti->id_lotissement)
                    ->where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
                    ->get();
                return [
                    'nom'         => $loti->nom,
                    'ville'       => $loti->ville,
                    'total'       => $lots->count(),
                    'disponibles' => $lots->where('statut', 'DISPONIBLE')->count(),
                    'reserves'    => $lots->where('statut', 'RESERVE')->count(),
                    'vendus'      => $lots->where('statut', 'VENDU')->count(),
                ];
            })
            ->filter(fn($l) => $l['total'] > 0)
            ->values();

        // ── Contrats ──────────────────────────────────────────────────────────
        $contratsEnCours       = Contrat::notDeleted()->where('statut', 'EN_COURS')->count();
        $contratsEnRetard      = Contrat::notDeleted()->where('statut', 'EN_RETARD')->count();
        $contratsSoldes        = Contrat::notDeleted()->where('statut', 'SOLDE')->count();
        $nbNouveauxContratsMois = Contrat::notDeleted()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();
        $montantVentesMois = Contrat::notDeleted()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('montant_total');

        // ── Paiements d'aujourd'hui ───────────────────────────────────────────
        $paiementsAujourdhui = Paiement::with(['contrat.client'])
            ->where('statut', 'VALIDE')
            ->whereDate('date_paiement', $today)
            ->where(fn($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('id_paiement', 'desc')
            ->get();

        // ── Échéances en retard (count) ───────────────────────────────────────
        $nbEcheancesEnRetard = Echeance::where('statut', 'EN_RETARD')
            ->where('valeursupprimee', '!=', 1)
            ->count();

        // ── Nouveaux contrats ce mois ─────────────────────────────────────────
        $nouveauxContratsMois = Contrat::notDeleted()
            ->with('client')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->orderBy('id_contrat', 'desc')
            ->take(8)
            ->get();

        return view('pages.dashboard.ecommerce', compact(
            'encaisseAujourdhui', 'encaisseMois', 'encaisseTotal',
            'paiementsJournaliers', 'paiementsParSemaine', 'paiementsParMode',
            'totalLots', 'lotsDisponibles', 'lotsReserves', 'lotsVendus',
            'lotsParLotissement',
            'contratsEnCours', 'contratsEnRetard', 'contratsSoldes',
            'nbNouveauxContratsMois', 'montantVentesMois',
            'paiementsAujourdhui', 'nbEcheancesEnRetard',
            'nouveauxContratsMois',
        ) + ['title' => 'Tableau de Bord']);
    }
}
