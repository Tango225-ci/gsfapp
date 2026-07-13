@php
    $client = $paiement->contrat->client;
    $lot = $paiement->contrat->lot;
    $isValide = $paiement->statut === 'VALIDE';
    $montantLettres = \App\Helpers\MontantHelper::enLettres((float) $paiement->montant);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<title>Reçu {{ $paiement->reference }}</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#f0f0f0;color:#1a1a1a;font-size:13px}

.page{max-width:740px;margin:0 auto;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.12)}
.top-bar{height:5px;background:#C8000A}

.header{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:20px;padding:20px 32px 16px;border-bottom:1px solid #ebebeb}
.logo-wrap img{height:56px;display:block}
.ref-block{text-align:right}
.ref-tag{font-size:9px;color:#aaa;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:4px}
.ref-num{font-size:16px;font-weight:800;color:#C8000A;letter-spacing:.5px}
.ref-date{font-size:11px;color:#888;margin-top:5px}

.title-area{text-align:center;padding:22px 32px 18px}
.title-deco{display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:8px}
.title-deco-line{width:60px;height:2px;background:#C8000A}
.title-deco-dot{width:7px;height:7px;background:#C8000A;border-radius:50%}
.title-main{font-size:21px;font-weight:700;color:#1a1a1a;text-transform:uppercase;letter-spacing:3px}
.title-sub{font-size:12px;color:#888;margin-top:6px;letter-spacing:.5px}

.body{padding:0 32px 28px}

.info-strip{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:#ebebeb;border:1px solid #ebebeb;border-radius:8px;overflow:hidden;margin-bottom:18px}
.info-cell{background:#fff;padding:14px 18px}
.info-cell.full{grid-column:span 2}
.ic-label{font-size:9px;color:#C8000A;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:5px}
.ic-val{font-size:13px;font-weight:600;color:#1a1a1a}
.ic-val.large{font-size:15px}
.ic-val.light{font-weight:400;color:#444}

.obj-card{background:#fff8f8;border:1px solid #fca5a5;border-radius:8px;padding:14px 18px;margin-bottom:18px;display:grid;grid-template-columns:1fr auto;align-items:center;gap:12px}
.obj-label{font-size:9px;color:#C8000A;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:5px}
.obj-val{font-size:13px;font-weight:700;color:#1a1a1a}
.obj-type-label{font-size:9px;color:#aaa;text-transform:uppercase;letter-spacing:1px;margin-bottom:3px}
.obj-type-val{font-size:12px;font-weight:700;color:#C8000A;background:#fff;border:1px solid #fca5a5;border-radius:20px;padding:3px 12px;display:inline-block}

.tbl-wrap{border-radius:8px;overflow:hidden;border:1px solid #ebebeb;margin-bottom:18px}
table{width:100%;border-collapse:collapse}
thead tr{background:#1a1a1a}
th{color:#fff;padding:11px 18px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase}
th:last-child{text-align:right}
td{padding:14px 18px;border-bottom:1px solid #f5f5f5;vertical-align:top}
td:last-child{text-align:right;font-weight:700;font-size:14px;color:#1a1a1a}
tr:last-child td{border-bottom:0}
.dsub{font-size:11px;color:#999;margin-top:4px;font-weight:400}
.dfont{font-size:13px;font-weight:600;color:#1a1a1a}
tr.total-row{background:#f9f9f9}
tr.total-row td:first-child{font-size:13px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:1px}
tr.total-row td:last-child{font-size:22px;font-weight:800;color:#C8000A}

.quittance{background:#fafafa;border-radius:8px;padding:16px 20px;margin-bottom:18px;position:relative;overflow:hidden}
.quittance::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:#C8000A}
.q-label{font-size:9px;color:#aaa;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px}
.q-val{font-size:14px;font-weight:700;color:#1a1a1a;font-style:italic}

.status-line{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:8px;margin-bottom:24px}
.sl-valide{background:#f0fdf4;border:1px solid #86efac}
.sl-attente{background:#fffbeb;border:1px solid #fcd34d}
.sl-label{font-size:11px;color:#555;font-weight:600}
.sl-badge{font-size:11px;font-weight:800;padding:4px 16px;border-radius:20px;letter-spacing:.5px}
.sl-badge.bv{background:#dcfce7;color:#15803d}
.sl-badge.ba{background:#fef9c3;color:#92400e}

.sigs{display:flex;justify-content:space-between;padding-top:22px;border-top:1px dashed #ddd}
.sig{text-align:center;width:44%}
.sig-role{font-size:12px;font-weight:700;color:#1a1a1a;margin-bottom:52px}
.sig-line{height:1px;background:#bbb;margin-bottom:8px}
.sig-note{font-size:10px;color:#aaa;line-height:1.6}

.footer{background:#1a1a1a;color:#666;padding:12px 32px;text-align:center;font-size:10px;letter-spacing:.3px}
.footer strong{color:#C8000A;font-weight:700}
.bottom-bar{height:5px;background:#C8000A}

.no-print-wrap{background:#f0f0f0;padding:14px 32px;display:flex;justify-content:flex-end}
.btn-print{background:#C8000A;color:#fff;padding:9px 22px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:700;letter-spacing:.5px}

@media print{
  body{background:#fff}
  .no-print-wrap{display:none}
  .page{box-shadow:none;max-width:100%}
  -webkit-print-color-adjust:exact;
  print-color-adjust:exact
}
</style>
</head>
<body>

<div class="no-print-wrap">
  <button class="btn-print" onclick="window.print()">&#128438;&nbsp; Imprimer</button>
</div>

<div class="page">
  <div class="top-bar"></div>

  <div class="header">
    <div class="logo-wrap">
      <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF" onerror="this.style.display='none'">
    </div>
    <div class="ref-block">
      <div class="ref-tag">Numéro de reçu</div>
      <div class="ref-num">{{ $paiement->reference }}</div>
      <div class="ref-date">Abidjan, le {{ now()->translatedFormat('d/m/Y') }}</div>
    </div>
  </div>

  <div class="title-area">
    <div class="title-deco">
      <div class="title-deco-line"></div>
      <div class="title-deco-dot"></div>
      <div class="title-main">Reçu de Paiement</div>
      <div class="title-deco-dot"></div>
      <div class="title-deco-line"></div>
    </div>
    <div class="title-sub">Document officiel &mdash; Fait foi entre les parties</div>
  </div>

  <div class="body">

    <div class="info-strip">
      <div class="info-cell full">
        <div class="ic-label">Reçu de</div>
        <div class="ic-val large">{{ $client->nom_prenoms ?? '-' }}</div>
      </div>
      <div class="info-cell">
        <div class="ic-label">Téléphone</div>
        <div class="ic-val">{{ $client->contact ?? '-' }}</div>
      </div>
      <div class="info-cell">
        <div class="ic-label">Date du paiement</div>
        <div class="ic-val">{{ $paiement->date_paiement_formatted }}</div>
      </div>
    </div>

    <div class="obj-card">
      <div>
        <div class="obj-label">Objet du paiement</div>
        <div class="obj-val">Contrat {{ $paiement->contrat->reference_contrat }} &mdash; Lot {{ $lot->numero_lot ?? '-' }}{{ $lot && $lot->numero_ilot ? ' — Îlot '.$lot->numero_ilot : '' }}</div>
      </div>
      <div>
        <div class="obj-type-label">Type</div>
        <div class="obj-type-val">{{ $paiement->type_paiement_label }}</div>
      </div>
    </div>

    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>Désignation</th>
            <th>Montant (FCFA)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="dfont">{{ $paiement->type_paiement === 'FRAIS_DOCUMENT' ? 'Frais document ACD' : 'Paiement mensualité' }}</div>
              <div class="dsub">Mode&nbsp;: {{ $paiement->mode_paiement_label }}@if($paiement->banque_paiement)&nbsp;&nbsp;|&nbsp;&nbsp;Banque&nbsp;: {{ $paiement->banque_label }}@endif</div>
            </td>
            <td>{{ $paiement->montant_formatted }}</td>
          </tr>
          <tr class="total-row">
            <td>Total</td>
            <td>{{ $paiement->montant_formatted }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="quittance">
      <div class="q-label">Arrêté la présente quittance de la somme de</div>
      <div class="q-val">{{ $montantLettres }}</div>
    </div>

    <div class="status-line {{ $isValide ? 'sl-valide' : 'sl-attente' }}">
      <span class="sl-label">Statut du paiement :</span>
      <span class="sl-badge {{ $isValide ? 'bv' : 'ba' }}">{{ $paiement->statut_label }}</span>
    </div>

    <div class="sigs">
      <div class="sig">
        <div class="sig-role">Le bénéficiaire</div>
        <div class="sig-line"></div>
        <div class="sig-note">(Signature précédée de la mention<br>&laquo;&nbsp;Bon pour encaissement&nbsp;&raquo;)</div>
      </div>
      <div class="sig">
        <div class="sig-role">Pour GSF</div>
        <div class="sig-line"></div>
        <div class="sig-note">(Signature et cachet)</div>
      </div>
    </div>

  </div>

  <div class="footer">
    Document généré automatiquement &mdash; <strong>GSF</strong> &mdash; {{ now()->translatedFormat('d/m/Y H:i') }}
  </div>
  <div class="bottom-bar"></div>

</div>

<script>setTimeout(function(){window.print()},600)</script>
</body>
</html>
