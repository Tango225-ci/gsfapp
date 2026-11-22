@php
    $client = $paiement->contrat->client;
    $lot = $paiement->contrat->lot;
    $isValide = $paiement->statut === 'VALIDE';
    $montantLettres = \App\Helpers\MontantHelper::enLettres((float) $paiement->montant);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Reçu {{ $paiement->reference }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color:#1a1a1a; }
table { border-collapse: collapse; width:100%; }

.top-bar { background:#C8000A; height:5px; margin-bottom:16px; }

.header-table td { vertical-align: middle; padding-bottom: 12px; border-bottom: 1px solid #ebebeb; }
.ref-cell { text-align:right; }
.ref-tag { font-size:8px; color:#aaa; text-transform:uppercase; letter-spacing:1px; }
.ref-num { font-size:16px; font-weight:bold; color:#C8000A; }
.ref-date { font-size:10px; color:#888; margin-top:4px; }

.title-area { text-align:center; padding: 18px 0; }
.title-main { font-size:19px; font-weight:bold; text-transform:uppercase; letter-spacing:2px; }
.title-sub { font-size:10px; color:#888; margin-top:5px; }

.info-table { margin-bottom:14px; }
.info-table td { border:1px solid #ebebeb; padding:10px 14px; width:50%; }
.ic-label { font-size:8px; color:#C8000A; text-transform:uppercase; font-weight:bold; }
.ic-val { font-size:13px; font-weight:bold; margin-top:4px; }

.obj-table { margin-bottom:14px; }
.obj-table td { border:1px solid #fca5a5; background:#fff8f8; padding:10px 14px; }
.obj-label { font-size:8px; color:#C8000A; text-transform:uppercase; font-weight:bold; }
.obj-val { font-size:12px; font-weight:bold; margin-top:4px; }
.obj-type-val { font-size:10px; font-weight:bold; color:#C8000A; border:1px solid #fca5a5; border-radius:10px; padding:3px 10px; }

.pay-table { margin-bottom:14px; }
.pay-table th { background:#1a1a1a; color:#fff; padding:9px 14px; font-size:9px; text-transform:uppercase; text-align:left; letter-spacing:0.5px; }
.pay-table th:last-child, .pay-table td:last-child { text-align:right; }
.pay-table td { padding:12px 14px; border-bottom:1px solid #f5f5f5; }
.pay-table .dsub { font-size:10px; color:#999; margin-top:3px; }
.pay-table .total-row td { background:#f9f9f9; font-weight:bold; text-transform:uppercase; font-size:11px; color:#555; }
.pay-table .total-row td:last-child { font-size:17px; color:#C8000A; }

.quittance { background:#fafafa; padding:12px 16px; margin-bottom:14px; }
.q-label { font-size:8px; color:#aaa; text-transform:uppercase; }
.q-val { font-size:13px; font-weight:bold; font-style:italic; margin-top:5px; }

.status-table { margin-bottom:24px; }
.status-table td { padding:10px 14px; }
.status-valide { background:#f0fdf4; }
.status-attente { background:#fffbeb; }
.sl-label { font-size:10px; color:#555; }
.sl-badge { font-size:10px; font-weight:bold; padding:3px 12px; border-radius:10px; }
.bv { background:#dcfce7; color:#15803d; }
.ba { background:#fef9c3; color:#92400e; }

.sig-table td { width:44%; text-align:center; padding-top:44px; border-top:1px solid #bbb; font-size:9px; color:#aaa; }
.sig-role { font-size:11px; font-weight:bold; color:#1a1a1a; padding-bottom:6px; }

.footer { text-align:center; font-size:9px; color:#888; margin-top:20px; padding-top:10px; border-top:1px solid #eee; }
.footer strong { color:#C8000A; }
.bottom-bar { background:#C8000A; height:5px; margin-top:10px; }
</style>
</head>
<body>

<div class="top-bar"></div>

<table class="header-table">
  <tr>
    <td><img src="{{ public_path('images/logo/gsf-logo.png') }}" style="height:48px;"></td>
    <td class="ref-cell">
      <div class="ref-tag">Numéro de reçu</div>
      <div class="ref-num">{{ $paiement->reference }}</div>
      <div class="ref-date">Abidjan, le {{ now()->translatedFormat('d/m/Y') }}</div>
    </td>
  </tr>
</table>

<div class="title-area">
  <div class="title-main">Reçu de Paiement</div>
  <div class="title-sub">Document officiel &mdash; Fait foi entre les parties</div>
</div>

<table class="info-table">
  <tr>
    <td colspan="2">
      <div class="ic-label">Reçu de</div>
      <div class="ic-val">{{ $client->nom_prenoms ?? '-' }}</div>
    </td>
  </tr>
  <tr>
    <td>
      <div class="ic-label">Téléphone</div>
      <div class="ic-val">{{ $client->contact ?? '-' }}</div>
    </td>
    <td>
      <div class="ic-label">Date du paiement</div>
      <div class="ic-val">{{ $paiement->date_paiement_formatted }}</div>
    </td>
  </tr>
</table>

<table class="obj-table">
  <tr>
    <td style="width:70%;">
      <div class="obj-label">Objet du paiement</div>
      <div class="obj-val">Contrat {{ $paiement->contrat->reference_contrat }} &mdash; Lot {{ $lot->numero_lot ?? '-' }}{{ $lot && $lot->numero_ilot ? ' — Îlot '.$lot->numero_ilot : '' }}</div>
    </td>
    <td style="text-align:right;">
      <span class="obj-type-val">{{ $paiement->type_paiement_label }}</span>
    </td>
  </tr>
</table>

<table class="pay-table">
  <thead>
    <tr><th>Désignation</th><th>Montant (FCFA)</th></tr>
  </thead>
  <tbody>
    <tr>
      <td>
        {{ $paiement->type_paiement === 'FRAIS_DOCUMENT' ? 'Frais document ACD' : 'Paiement mensualité' }}
        <div class="dsub">Mode : {{ $paiement->mode_paiement_label }}@if($paiement->banque_paiement) &nbsp;|&nbsp; Banque : {{ $paiement->banque_label }}@endif</div>
      </td>
      <td>{{ $paiement->montant_formatted }}</td>
    </tr>
    <tr class="total-row">
      <td>Total</td>
      <td>{{ $paiement->montant_formatted }}</td>
    </tr>
  </tbody>
</table>

<div class="quittance">
  <div class="q-label">Arrêté la présente quittance de la somme de</div>
  <div class="q-val">{{ $montantLettres }}</div>
</div>

<table class="status-table">
  <tr class="{{ $isValide ? 'status-valide' : 'status-attente' }}">
    <td>
      <span class="sl-label">Statut du paiement : </span>
      <span class="sl-badge {{ $isValide ? 'bv' : 'ba' }}">{{ $paiement->statut_label }}</span>
    </td>
  </tr>
</table>

<table class="sig-table">
  <tr>
    <td><div class="sig-role">Le bénéficiaire</div>(Signature précédée de la mention « Bon pour encaissement »)</td>
    <td></td>
    <td><div class="sig-role">Pour GSF</div>(Signature et cachet)</td>
  </tr>
</table>

<div class="footer">
  Document généré automatiquement &mdash; <strong>GSF</strong> &mdash; {{ now()->translatedFormat('d/m/Y H:i') }}
</div>
<div class="bottom-bar"></div>

</body>
</html>
