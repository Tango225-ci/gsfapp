@php
    $client = $contrat->client;
    $lot = $contrat->lot;
    $statusClass = $contrat->statut === 'SOLDE' ? 'status-solde' : ($contrat->statut === 'EN_RETARD' ? 'status-retard' : 'status-encours');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Contrat {{ $contrat->reference_contrat }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; color:#1f2937; font-size: 11px; }
table { border-collapse: collapse; width:100%; }

.letterhead-table td { vertical-align: middle; padding-bottom: 14px; border-bottom: 3px solid #dc2626; }
.letterhead-meta { text-align:right; font-size:9px; color:#6b7280; }

h1 { color:#dc2626; font-size:19px; margin: 16px 0 12px; }
h2 { color:#374151; font-size:14px; margin: 18px 0 10px; border-bottom: 2px solid #dc2626; padding-bottom: 6px; }

.info-table { background:#f9fafb; margin-bottom:16px; }
.info-table td { padding: 10px 14px; width:50%; }
.label { font-size:9px; color:#6b7280; text-transform:uppercase; }
.value { font-size:12px; font-weight:bold; color:#111827; }

.amount-box { background:#7f1d1d; color:#fff; padding:14px; margin-bottom:16px; }
.amount-table td { text-align:center; padding: 4px 8px; width:33%; }
.amount-table h4 { font-size:10px; font-weight:normal; margin-bottom:4px; }
.amount-table .number { font-size:17px; font-weight:bold; }
.progress-row { padding-top:10px; }
.progress-outer { background:rgba(255,255,255,0.3); height:8px; }
.progress-inner { background:#10b981; height:8px; }

table.data { margin-bottom:16px; }
table.data th { background:#f3f4f6; padding:8px 10px; text-align:left; font-size:10px; font-weight:bold; border-bottom:2px solid #e5e7eb; }
table.data td { padding:8px 10px; border-bottom:1px solid #e5e7eb; font-size:10px; }
.text-right { text-align:right; }
.text-center { text-align:center; }

.status-badge { font-size:9px; font-weight:bold; padding:2px 8px; border-radius:8px; }
.status-solde { background:#d1fae5; color:#065f46; }
.status-encours { background:#fef3c7; color:#92400e; }
.status-retard { background:#fee2e2; color:#991b1b; }
.status-partiel { background:#fed7aa; color:#9a3412; }
.status-nonpaye { background:#fee2e2; color:#991b1b; }
.status-attente { background:#f3f4f6; color:#374151; }
.status-disponible { background:#d1fae5; color:#065f46; }
.status-remis { background:#dbeafe; color:#1e40af; }

.footer { margin-top:24px; text-align:center; font-size:9px; color:#9ca3af; border-top:2px solid #dc2626; padding-top:10px; }
.footer .footer-company { font-weight:bold; color:#374151; margin-bottom:3px; }
</style>
</head>
<body>

<table class="letterhead-table">
  <tr>
    <td><img src="{{ public_path('images/logo/gsf-logo.png') }}" style="height:44px;"></td>
    <td class="letterhead-meta">
      Yopougon Agbayate, Résidence Amado<br>
      contact@geometresonfemme.com
    </td>
  </tr>
</table>

<h1>État du Contrat {{ $contrat->reference_contrat }}</h1>

<h2>Informations Générales</h2>
<table class="info-table">
  <tr>
    <td><span class="label">Client</span><br><span class="value">{{ $client->nom_prenoms ?? '-' }}</span></td>
    <td><span class="label">Lot</span><br><span class="value">{{ $lot->numero_lot ?? '-' }}{{ $lot && $lot->lotissement_nom ? ' — '.$lot->lotissement_nom : '' }}</span></td>
  </tr>
  <tr>
    <td><span class="label">Date</span><br><span class="value">{{ $contrat->date_contrat_formatted }}</span></td>
    <td><span class="label">Type</span><br><span class="value">{{ $contrat->type_paiement_label }}</span></td>
  </tr>
  <tr>
    <td colspan="2"><span class="label">Statut</span><br><span class="status-badge {{ $statusClass }}">{{ $contrat->statut_label }}</span></td>
  </tr>
</table>

<h2>Situation des Paiements</h2>
<div class="amount-box">
  <table class="amount-table">
    <tr>
      <td><h4>Montant total</h4><div class="number">{{ $contrat->montant_total_formatted }}</div></td>
      <td><h4>Déjà payé</h4><div class="number" style="color:#bbf7d0">{{ $contrat->montant_paye_formatted }}</div></td>
      <td><h4>Reste à payer</h4><div class="number" style="color:{{ $contrat->reste_a_payer_calcule > 0 ? '#fde68a' : '#bbf7d0' }}">{{ $contrat->reste_a_payer_formatted }}</div></td>
    </tr>
  </table>
  <table class="progress-row">
    <tr><td style="color:#fff;font-size:10px;">Progression</td><td style="color:#fff;font-size:10px;text-align:right;">{{ $contrat->progression_paiement }}%</td></tr>
  </table>
  <div class="progress-outer"><div class="progress-inner" style="width:{{ $contrat->progression_paiement }}%"></div></div>
</div>

@if ($contrat->type_paiement === 'ECHELONNE' && $contrat->echeances->isNotEmpty())
<h2>Échéances</h2>
<table class="data">
  <thead>
    <tr><th>N°</th><th>Date</th><th class="text-right">Montant prévu</th><th class="text-right">Payé</th><th class="text-right">Reste</th><th class="text-center">Statut</th></tr>
  </thead>
  <tbody>
    @foreach ($contrat->echeances->sortBy('date_echeance') as $echeance)
    <tr>
      <td>{{ $echeance->numero_echeance }}</td>
      <td>{{ $echeance->date_echeance_formatted }}</td>
      <td class="text-right">{{ $echeance->montant_prevu_formatted }}</td>
      <td class="text-right">{{ $echeance->montant_paye_formatted }}</td>
      <td class="text-right">{{ $echeance->reste_a_payer_formatted }}</td>
      <td class="text-center">
        <span class="status-badge {{ $echeance->statut === 'PAYE' ? 'status-solde' : ($echeance->statut === 'EN_RETARD' ? 'status-retard' : 'status-encours') }}">{{ $echeance->statut_label }}</span>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

@if ($contrat->contratDocuments->isNotEmpty())
<h2>Documents ACD</h2>
<table class="data">
  <thead>
    <tr><th>Document</th><th class="text-right">Montant</th><th class="text-center">Statut paiement</th><th class="text-center">Statut document</th></tr>
  </thead>
  <tbody>
    @foreach ($contrat->contratDocuments as $document)
    @php
        $paiementClass = $document->statut_paiement === 'paye' ? 'status-solde' : ($document->statut_paiement === 'partiel' ? 'status-partiel' : 'status-nonpaye');
        $documentClass = match ($document->statut_document) {
            'remis' => 'status-remis',
            'disponible' => 'status-disponible',
            'paye' => 'status-encours',
            default => 'status-attente',
        };
    @endphp
    <tr>
      <td>{{ $document->document_type_nom ?? '-' }}</td>
      <td class="text-right">{{ $document->montant_formatted }}</td>
      <td class="text-center"><span class="status-badge {{ $paiementClass }}">{{ $document->statut_paiement_label }}</span></td>
      <td class="text-center"><span class="status-badge {{ $documentClass }}">{{ $document->statut_document_label }}</span></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

@if ($contrat->paiements->isNotEmpty())
<h2>Historique des Paiements</h2>
<table class="data">
  <thead>
    <tr><th>Date</th><th>Référence</th><th>Type</th><th>Mode</th><th class="text-right">Montant</th><th class="text-center">Statut</th></tr>
  </thead>
  <tbody>
    @foreach ($contrat->paiements as $paiement)
    <tr>
      <td>{{ $paiement->date_paiement_formatted }}</td>
      <td style="font-size:9px;">{{ $paiement->reference }}</td>
      <td>{{ $paiement->type_paiement_label }}</td>
      <td>{{ $paiement->mode_paiement_label }}</td>
      <td class="text-right">{{ $paiement->montant_formatted }}</td>
      <td class="text-center"><span class="status-badge status-solde">{{ $paiement->statut_label }}</span></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">
  <div class="footer-company">Géomètre Son Femme SARL</div>
  <div>Yopougon Agbayate, Résidence Amado — contact@geometresonfemme.com</div>
  <div style="margin-top:6px">Document généré automatiquement le {{ now()->translatedFormat('d/m/Y H:i') }}</div>
</div>

</body>
</html>
