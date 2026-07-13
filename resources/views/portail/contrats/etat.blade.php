@php
    $client = $contrat->client;
    $lot = $contrat->lot;
    $statusClass = $contrat->statut === 'SOLDE' ? 'status-solde' : ($contrat->statut === 'EN_RETARD' ? 'status-retard' : 'status-encours');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<title>Contrat {{ $contrat->reference_contrat }}</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;margin:30px;background:#fff;color:#1f2937}
.letterhead{display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #dc2626;padding-bottom:16px;margin-bottom:25px}
.letterhead-brand{display:flex;align-items:center;gap:14px}
.letterhead-brand img{height:50px;display:block}
.letterhead-meta{text-align:right;font-size:11px;color:#6b7280;line-height:1.5}
h1{color:#dc2626;font-size:24px;margin-bottom:14px}
h2{color:#374151;font-size:18px;margin:25px 0 15px;border-bottom:2px solid #dc2626;padding-bottom:8px}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px;background:#f9fafb;padding:20px;border-radius:10px;margin-bottom:20px}
.label{font-size:12px;color:#6b7280;text-transform:uppercase}
.value{font-size:16px;font-weight:600;color:#111827}
.amount-box{background:linear-gradient(135deg,#7f1d1d,#dc2626);color:#fff;padding:20px;border-radius:10px;margin-bottom:20px}
.amount-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;text-align:center}
.amount-item h4{font-size:13px;font-weight:400;margin-bottom:5px}
.amount-item .number{font-size:24px;font-weight:700}
.progress-bar{width:100%;height:10px;background:rgba(255,255,255,.3);border-radius:5px;margin:10px 0}
.progress-fill{height:100%;background:#10b981;border-radius:5px}
table{width:100%;border-collapse:collapse;margin-top:15px;margin-bottom:20px}
th{background:#f3f4f6;padding:12px;text-align:left;font-size:13px;font-weight:600;border-bottom:2px solid #e5e7eb}
td{padding:12px;border-bottom:1px solid #e5e7eb}
tbody tr:nth-child(even){background:#fafafa}
.text-right{text-align:right}
.status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.status-solde{background:#d1fae5;color:#065f46}
.status-encours{background:#fef3c7;color:#92400e}
.status-retard{background:#fee2e2;color:#991b1b}
.status-partiel{background:#fed7aa;color:#9a3412}
.status-nonpaye{background:#fee2e2;color:#991b1b}
.status-attente{background:#f3f4f6;color:#374151}
.status-disponible{background:#d1fae5;color:#065f46}
.status-remis{background:#dbeafe;color:#1e40af}
.footer{margin-top:40px;text-align:center;font-size:12px;color:#9ca3af;border-top:2px solid #dc2626;padding-top:16px}
.footer .footer-company{font-weight:700;color:#374151;margin-bottom:4px}
.print-btn{background:#dc2626;color:#fff;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;margin-bottom:20px;font-size:16px}
.table-wrap{width:100%;overflow-x:auto;margin-top:15px;margin-bottom:20px;-webkit-overflow-scrolling:touch}
.table-wrap table{margin-top:0;margin-bottom:0;min-width:520px}
@media print{body{margin:15px}.print-btn{display:none}.amount-box{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
@media (max-width:640px){
    body{margin:14px}
    .letterhead{flex-direction:column;align-items:flex-start;gap:10px}
    .letterhead-meta{text-align:left}
    h1{font-size:19px}
    h2{font-size:15px;margin:20px 0 12px}
    .grid{grid-template-columns:1fr;padding:14px;gap:10px}
    .amount-box{padding:14px}
    .amount-grid{grid-template-columns:1fr;gap:12px;text-align:left}
    .amount-item .number{font-size:20px}
}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimer</button>

<div class="letterhead">
    <div class="letterhead-brand">
        <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF">
    </div>
    <div class="letterhead-meta">
        <div>Yopougon Agbayate, Résidence Amado</div>
        <div>contact@geometresonfemme.com</div>
    </div>
</div>

<h1>État du Contrat {{ $contrat->reference_contrat }}</h1>

<h2>Informations Générales</h2>
<div class="grid">
    <div><span class="label">Client : </span><span class="value">{{ $client->nom_prenoms ?? '-' }}</span></div>
    <div><span class="label">Lot : </span><span class="value">{{ $lot->numero_lot ?? '-' }}{{ $lot && $lot->lotissement_nom ? ' — '.$lot->lotissement_nom : '' }}</span></div>
    <div><span class="label">Date : </span><span class="value">{{ $contrat->date_contrat_formatted }}</span></div>
    <div><span class="label">Type : </span><span class="value">{{ $contrat->type_paiement_label }}</span></div>
    <div><span class="label">Statut : </span><span class="value"><span class="status-badge {{ $statusClass }}">{{ $contrat->statut_label }}</span></span></div>
</div>

<h2>Situation des Paiements</h2>
<div class="amount-box">
    <div class="amount-grid">
        <div class="amount-item"><h4>Montant total</h4><div class="number">{{ $contrat->montant_total_formatted }}</div></div>
        <div class="amount-item"><h4>Déjà payé</h4><div class="number" style="color:#bbf7d0">{{ $contrat->montant_paye_formatted }}</div></div>
        <div class="amount-item"><h4>Reste à payer</h4><div class="number" style="color:{{ $contrat->reste_a_payer_calcule > 0 ? '#fde68a' : '#bbf7d0' }}">{{ $contrat->reste_a_payer_formatted }}</div></div>
    </div>
    <div style="margin-top:15px">
        <div style="display:flex;justify-content:space-between;font-size:13px"><span>Progression</span><span>{{ $contrat->progression_paiement }}%</span></div>
        <div class="progress-bar"><div class="progress-fill" style="width:{{ $contrat->progression_paiement }}%"></div></div>
    </div>
</div>

@if ($contrat->type_paiement === 'ECHELONNE' && $contrat->echeances->isNotEmpty())
<h2>Échéances</h2>
<div class="table-wrap">
<table>
    <thead>
        <tr><th>N°</th><th>Date</th><th class="text-right">Montant prévu</th><th class="text-right">Payé</th><th class="text-right">Reste</th><th style="text-align:center">Statut</th></tr>
    </thead>
    <tbody>
        @foreach ($contrat->echeances as $echeance)
        <tr>
            <td>{{ $echeance->numero_echeance }}</td>
            <td>{{ $echeance->date_echeance_formatted }}</td>
            <td class="text-right">{{ $echeance->montant_prevu_formatted }}</td>
            <td class="text-right">{{ $echeance->montant_paye_formatted }}</td>
            <td class="text-right">{{ $echeance->reste_a_payer_formatted }}</td>
            <td style="text-align:center">
                <span class="status-badge {{ $echeance->statut === 'PAYE' ? 'status-solde' : ($echeance->statut === 'EN_RETARD' ? 'status-retard' : 'status-encours') }}">{{ $echeance->statut_label }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif

@if ($contrat->contratDocuments->isNotEmpty())
<h2>Documents ACD</h2>
<div class="table-wrap">
<table>
    <thead>
        <tr><th>Document</th><th class="text-right">Montant</th><th style="text-align:center">Statut paiement</th><th style="text-align:center">Statut document</th></tr>
    </thead>
    <tbody>
        @foreach ($contrat->contratDocuments as $document)
        <tr>
            <td>{{ $document->document_type_nom ?? '-' }}</td>
            <td class="text-right">{{ $document->montant_formatted }}</td>
            <td style="text-align:center">
                @php
                    $paiementClass = $document->statut_paiement === 'paye' ? 'status-solde' : ($document->statut_paiement === 'partiel' ? 'status-partiel' : 'status-nonpaye');
                @endphp
                <span class="status-badge {{ $paiementClass }}">{{ $document->statut_paiement_label }}</span>
            </td>
            <td style="text-align:center">
                @php
                    $documentClass = match ($document->statut_document) {
                        'remis' => 'status-remis',
                        'disponible' => 'status-disponible',
                        'paye' => 'status-encours',
                        default => 'status-attente',
                    };
                @endphp
                <span class="status-badge {{ $documentClass }}">{{ $document->statut_document_label }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif

@if ($contrat->paiements->isNotEmpty())
<h2>Historique des Paiements</h2>
<div class="table-wrap">
<table>
    <thead>
        <tr><th>Date</th><th>Référence</th><th>Type</th><th>Mode</th><th class="text-right">Montant</th><th style="text-align:center">Statut</th></tr>
    </thead>
    <tbody>
        @foreach ($contrat->paiements as $paiement)
        <tr>
            <td>{{ $paiement->date_paiement_formatted }}</td>
            <td style="font-size:13px">{{ $paiement->reference }}</td>
            <td>{{ $paiement->type_paiement_label }}</td>
            <td>{{ $paiement->mode_paiement_label }}</td>
            <td class="text-right">{{ $paiement->montant_formatted }}</td>
            <td style="text-align:center">
                <span class="status-badge status-solde">{{ $paiement->statut_label }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif

<div class="footer">
    <div class="footer-company">Géomètre Son Femme SARL</div>
    <div>Yopougon Agbayate, Résidence Amado — contact@geometresonfemme.com</div>
    <div style="margin-top:8px">Document généré automatiquement le {{ now()->translatedFormat('d/m/Y H:i') }}</div>
</div>

</body>
</html>
