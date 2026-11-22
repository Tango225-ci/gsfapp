<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\DocumentType;
use App\Models\Echeance;
use App\Models\Lot;
use App\Models\Lotissement;
use App\Models\Paiement;
use App\Models\Utilisateur;
use App\Models\Visite;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Ressource de permission à vérifier pour chaque entité exportable.
     */
    private const PERMISSIONS = [
        'clients'      => 'clients',
        'lotissements' => 'lotissements',
        'lots'         => 'lots',
        'contrats'     => 'contrats',
        'paiements'    => 'paiements',
        'visites'      => 'visites',
        'echeances'    => 'echeances',
        'utilisateurs' => 'utilisateurs',
        'documents'    => 'documents',
    ];

    public function export(string $entity): StreamedResponse
    {
        abort_if(!isset(self::PERMISSIONS[$entity]), 404);

        PermissionHelper::abort403(self::PERMISSIONS[$entity], 'view');

        [$headers, $rows] = match ($entity) {
            'clients'      => $this->exportClients(),
            'lotissements' => $this->exportLotissements(),
            'lots'         => $this->exportLots(),
            'contrats'     => $this->exportContrats(),
            'paiements'    => $this->exportPaiements(),
            'visites'      => $this->exportVisites(),
            'echeances'    => $this->exportEcheances(),
            'utilisateurs' => $this->exportUtilisateurs(),
            'documents'    => $this->exportDocuments(),
        };

        return $this->streamXlsx($entity, $headers, $rows);
    }

    // ── Exporteurs ───────────────────────────────────────────────────────────

    private function exportClients(): array
    {
        $headers = ['Référence', 'Nom et prénoms', 'Contact', 'Email', 'Adresse', 'Pièce identité', 'N° pièce', 'Statut', 'Solde compte'];

        $rows = Client::where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('nom_prenoms')
            ->get()
            ->map(fn ($c) => [
                $c->reference_client, $c->nom_prenoms, $c->contact, $c->email, $c->adresse,
                $c->piece_identite, $c->numero_piece, $c->statut, (float) $c->solde_compte,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportLotissements(): array
    {
        $headers = ['Nom', 'Ville', 'Localisation', 'Statut', 'N° approbation', 'Date approbation', 'Nb lots'];

        $rows = Lotissement::withCount('lots')
            ->where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('nom')
            ->get()
            ->map(fn ($l) => [
                $l->nom, $l->ville, $l->localisation, $l->statut_lotissement,
                $l->numero_approbation, optional($l->date_approbation)->format('d/m/Y'), $l->lots_count,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportLots(): array
    {
        $headers = ['N° lot', 'Îlot', 'Lotissement', 'Type', 'Superficie (m²)', 'Prix comptant', 'Prix échelonné', 'Prix achat', 'Propriétaire', 'Statut'];

        $rows = Lot::with(['lotissement', 'proprietaire'])
            ->where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('numero_lot')
            ->get()
            ->map(fn ($l) => [
                $l->numero_lot, $l->numero_ilot, $l->lotissement->nom ?? '', $l->type_lot,
                (float) $l->superficie, (float) $l->prix_comptant, (float) $l->prix_echelonne,
                (float) $l->prix_achat, $l->proprietaire->nom ?? '', $l->statut,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportContrats(): array
    {
        $headers = ['Référence', 'Client', 'N° lot', 'Date contrat', 'Type paiement', 'Montant total', 'Montant payé', 'Reste à payer', 'Statut'];

        $rows = Contrat::with(['client', 'lot'])
            ->where('valeursupprimee', '!=', 1)
            ->orderByDesc('date_contrat')
            ->get()
            ->map(fn ($c) => [
                $c->reference_contrat, $c->client->nom_prenoms ?? '', $c->lot->numero_lot ?? '',
                optional($c->date_contrat)->format('d/m/Y'), $c->type_paiement_label,
                (float) $c->montant_total, (float) $c->montant_paye, (float) $c->reste_a_payer_calcule,
                $c->statut_label,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportPaiements(): array
    {
        $headers = ['Référence', 'Contrat', 'Client', 'Date paiement', 'Montant', 'Mode paiement', 'Statut'];

        $rows = Paiement::with(['contrat.client'])
            ->where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderByDesc('date_paiement')
            ->get()
            ->map(fn ($p) => [
                $p->reference, $p->contrat->reference_contrat ?? '', $p->contrat->client->nom_prenoms ?? '',
                optional($p->date_paiement)->format('d/m/Y'), (float) $p->montant,
                $p->mode_paiement_label, $p->statut_label,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportVisites(): array
    {
        $headers = ['Nom visiteur', 'Contact', 'Adresse', 'Date visite', 'Heure arrivée', 'Heure départ', 'Motif'];

        $rows = Visite::where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderByDesc('date_visite')
            ->get()
            ->map(fn ($v) => [
                $v->nom_visiteur, $v->contact_visiteur, $v->adresse_visiteur,
                optional($v->date_visite)->format('d/m/Y'), $v->heure_arrivee, $v->heure_depart, $v->motif,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportEcheances(): array
    {
        $headers = ['N° échéance', 'Contrat', 'Client', 'Date échéance', 'Montant prévu', 'Montant payé', 'Reste', 'Statut'];

        $rows = Echeance::with(['contrat.client'])
            ->where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('date_echeance')
            ->get()
            ->map(fn ($e) => [
                $e->numero_echeance, $e->contrat->reference_contrat ?? '', $e->contrat->client->nom_prenoms ?? '',
                optional($e->date_echeance)->format('d/m/Y'), (float) $e->montant_prevu,
                (float) $e->montant_paye, (float) $e->reste_a_payer, $e->statut_label,
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportUtilisateurs(): array
    {
        $headers = ['Nom', 'Identifiant', 'Email', 'Rôle', 'Statut'];

        $rows = Utilisateur::orderBy('nom')
            ->get()
            ->map(fn ($u) => [
                $u->nom, $u->login, $u->email, $u->role, $u->statut ? 'Actif' : 'Désactivé',
            ])->toArray();

        return [$headers, $rows];
    }

    private function exportDocuments(): array
    {
        $headers = ['Type de document', 'Ordre affichage', 'Obligatoire', 'Actif'];

        $rows = DocumentType::where(fn ($q) => $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee'))
            ->orderBy('ordre_affichage')
            ->get()
            ->map(fn ($d) => [
                $d->nom, $d->ordre_affichage, $d->est_obligatoire ? 'Oui' : 'Non', $d->actif ? 'Oui' : 'Non',
            ])->toArray();

        return [$headers, $rows];
    }

    // ── Génération du fichier ────────────────────────────────────────────────

    private function streamXlsx(string $filename, array $headers, array $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        $lastColumn = $sheet->getHighestColumn();

        $headerStyle = $sheet->getStyle('A1:' . $lastColumn . '1');
        $headerStyle->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DC2626');

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $downloadName = $filename . '_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
